#!/usr/bin/env python3
"""Real application SQL/HTTP workflows with a CLI-only synthetic payment boundary.
No external Authorize.net request, account key or genuine transaction is used.
"""
from __future__ import annotations
import concurrent.futures
import hashlib
import hmac
import json
import os
import re
import secrets
import subprocess
import tempfile
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path
from http_integration import ROOT, Client, build_package, expect, site
from content_integration import token
from commerce_integration import seed_catalog, adjust, snapshot as catalog_snapshot, signup
from communication_integration import enable_fixture_smtp

SIGNATURE_KEY = 'A1' * 64


def fixture(s, action, values=None):
    env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot),'SPIRIT_DISABLE_PAYMENT_NETWORK':'1'}
    r=subprocess.run(['php',str(ROOT/'tests/payment_fixture.php'),action],input=json.dumps(values or {}),text=True,capture_output=True,env=env,timeout=35)
    if r.returncode:
        raise AssertionError('Payment fixture failed: '+r.stderr)
    return json.loads(r.stdout)


def snapshot(s):
    return fixture(s,'snapshot')


def run_payment_task(s):
    env={**s.env,'SPIRIT_DISABLE_PAYMENT_NETWORK':'1'}
    result=subprocess.run(
        ['php',str(s.root/'tasks/payments.php'),'--document-root='+str(s.docroot)],
        env=env,text=True,capture_output=True,timeout=35
    )
    expect(result.returncode==0,'Actual scheduled payment CLI task completes without provider networking')
    payload=json.loads(result.stdout)
    expect(payload.get('heartbeat') is True,'Successful payment maintenance reports heartbeat persistence')
    path=s.runtime/'cache/task-payments.json'
    expect(path.is_file() and (path.stat().st_mode & 0o077)==0,'Payment heartbeat is stored only in protected private runtime cache')
    stamp=json.loads(path.read_text())
    expect(isinstance(stamp.get('ran_at'),str) and stamp['ran_at'].endswith('Z'),'Payment heartbeat contains only a UTC completion timestamp')


def order(s, oid):
    return next(o for o in snapshot(s)['orders'] if int(o['id'])==oid)


def configure(s):
    policy={'shipping':'5.00','rates':'LA:8.125\nFL:0','zip_rates':'LA:70501:10','policy':'Synthetic test-only shipping and returns terms. Not live legal policies.','reviewed':'1'}
    expect(s.client.request('admin/orders/policy.php',{**policy,'csrf':'bad'})[0]==403,'Delivery policy requires owner CSRF')
    expect(s.client.form('admin/orders/policy.php',policy)[0]==200,'Owner saves explicit shipping and synthetic rate configuration')
    values={'action':'save','environment':'sandbox','login_id':'FIXTURE_LOGIN','client_key':'FIXTURE_PUBLIC_KEY','transaction_key':'SYNTHETIC_NOT_REAL_TRANSACTION_KEY','signature_key':SIGNATURE_KEY,'enabled':'1','owner_password':s.password}
    response=s.client.form('admin/orders/payments.php',{**values,'owner_password':'wrong'})
    expect(not snapshot(s)['payments']['enabled'],'Incorrect owner reauthentication cannot enable payments')
    response=s.client.form('admin/orders/payments.php',values)
    settings=snapshot(s)['payments']
    expect(response[0]==200 and settings['enabled'] and settings['environment']=='sandbox','Owner enables only the configured sandbox environment')
    expect(settings['sandbox']['transaction_key'].startswith('v1:') and 'SYNTHETIC_NOT_REAL_TRANSACTION_KEY' not in json.dumps(settings),'Transaction Key is encrypted at rest')
    expect(settings['sandbox']['signature_key'].startswith('v1:') and SIGNATURE_KEY not in response[1],'Signature Key is encrypted and never reflected in a settings page')
    old=settings['sandbox']['transaction_key']
    s.client.form('admin/orders/payments.php',{**values,'transaction_key':'','signature_key':''})
    expect(snapshot(s)['payments']['sandbox']['transaction_key']==old,'Blank secret fields preserve the encrypted credentials')
    s.client.form('admin/orders/payments.php',{**values,'login_id':'OTHER_MERCHANT'})
    expect(snapshot(s)['payments']['sandbox']['login_id']=='FIXTURE_LOGIN','Merchant identity cannot be silently changed')
    s.client.form('admin/orders/payments.php?environment=production',{**values,'environment':'production'})
    expect(snapshot(s)['payments']['environment']=='sandbox','Production payment enablement requires an explicit live confirmation')


def bag(s,vid,qty=1,client=None):
    client=client or Client(s.base)
    response=client.request('shop/product.php?slug=fixture-shirt')
    values={'action':'add','variant_id':str(vid),'quantity':str(qty),'nonce':secrets.token_hex(32),'csrf':token(response[1])}
    result=client.request('shop/cart.php',values)
    expect(result[0]==200 and 'Continue to checkout' in result[1],'Eligible bag exposes checkout when configured')
    return client


def checkout_values(nonce=None):
    return {'nonce':nonce or secrets.token_hex(32),'email':'guest@example.test','recipient':'Synthetic Guest','line_one':'123 Fixture Street','line_two':'','city':'Lafayette','region':'LA','postal_code':'70501','billing_same':'1','order_consent':'1'}


def prepare(s,vid,qty=1,client=None):
    client=bag(s,vid,qty,client)
    response=client.request('shop/checkout.php');values={**checkout_values(),'csrf':token(response[1])}
    response=client.request('shop/checkout.php',values)
    reference=urllib.parse.parse_qs(urllib.parse.urlparse(response[3]).query).get('reference',[''])[0]
    expect(reference.startswith('SG-') and response[0]==200,'Checkout prepares a private order review before charging')
    record=next(o for o in snapshot(s)['orders'] if o['reference']==reference)
    return client,int(record['id']),values,response


def post_webhook(s,body,valid=True,mode='sandbox'):
    raw=json.dumps(body,separators=(',',':')).encode()
    signature='sha512='+hmac.new(SIGNATURE_KEY.encode(),raw,hashlib.sha512).hexdigest()
    if not valid:
        signature='sha512'+'='+('0'*128)
    request=urllib.request.Request(s.base+'payments/'+mode+'.php',data=raw,headers={'Content-Type':'application/json','X-ANET-Signature':signature})
    try: r=urllib.request.urlopen(request,timeout=20)
    except urllib.error.HTTPError as e: r=e
    with r:
        return r.status,r.read().decode(),dict(r.headers)


def notification(s,oid,transaction,notification_id=None):
    o=order(s,oid)
    op=next(x for x in snapshot(s)['payment_operations'] if int(x['order_id'])==oid and x['kind']=='charge')
    return {'notificationId':notification_id or secrets.token_hex(16),'eventType':'net.authorize.payment.authcapture.created','payload':{'id':transaction,'entityName':'transaction','merchantReferenceId':op['reference'],'authAmount':0.01}}


def money_and_privacy(s,pid,vid):
    start=int(catalog_snapshot(s)['product_variants'][0]['stock'])
    client,oid,values,response=prepare(s,vid,2)
    o=order(s,oid)
    expect(int(o['total_cents'])==6002 and '$60.02' in response[1],'Review total includes integer-cent items, shipping and configured tax')
    expect(o['payment_state']=='awaiting_payment' and o['reservation_state']=='held','Preparing checkout reserves stock but does not claim payment')
    expect(int(catalog_snapshot(s)['product_variants'][0]['stock'])==start-2,'Inventory is reserved exactly once when an order is prepared')
    client.request('shop/checkout.php',values)
    expect(len(snapshot(s)['orders'])==1 and int(catalog_snapshot(s)['product_variants'][0]['stock'])==start-2,'Repeated checkout POST cannot create a second order or reservation')
    expect(Client(s.base).request('shop/order.php?reference='+o['reference'])[0]==404,'An order reference alone does not expose a private receipt')
    expect('awaiting_payment'==order(s,oid)['payment_state'] and client.request('shop/order.php?reference='+o['reference']+'&paid=1&responseCode=1')[0]==200,'Forged browser return parameters cannot mark an order paid')
    expect(order(s,oid)['payment_state']=='awaiting_payment','Browser return remains informational, not payment evidence')
    csp=response[2].get('Content-Security-Policy','')
    expect('jstest.authorize.net' in csp and 'unsafe-eval' not in csp and 'unsafe-inline' not in csp,'Payment page permits only its official SDK environment without weakening script execution')
    for field in ['card-holder','card-number','card-month','card-year','card-code']:
        tag=re.search(r'<input[^>]*id="'+field+r'"[^>]*>',response[1]).group(0)
        expect(' name=' not in tag,'Sensitive browser field is never a native submitted field: '+field)
    pay='shop/pay.php?reference='+o['reference']
    expect(client.request(pay,{'csrf':'wrong'})[0]==403,'Tokenized payment endpoint rejects invalid CSRF')
    bad={'csrf':token(response[1]),'nonce':secrets.token_hex(32),'data_descriptor':'COMMON.ACCEPT.INAPP.PAYMENT','data_value':'SYNTHETIC_OPAQUE_TOKEN_NOT_A_CARD_0001','card_number':'4111111111111111'}
    result=client.request(pay,bad)
    expect('Raw card data' in result[1] and snapshot(s)['payment_operations']==[],'Raw-card-field submission is rejected before an operation is created')
    expect('4111111111111111' not in json.dumps(snapshot(s)),'Rejected card input is not stored in order or payment records')
    # Two separate PHP processes race the same persisted order. Only one may cross
    # the synthetic irreversible boundary, regardless of the posted nonce.
    with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
        results=list(pool.map(lambda _:fixture(s,'pay',{'id':oid,'delay':250000}),[0,1]))
    expect(sum(r['mutating_calls'] for r in results)==1,'Concurrent payment submissions cross the gateway boundary only once')
    expect(order(s,oid)['payment_state']=='paid' and order(s,oid)['reservation_state']=='consumed','Positive authenticated-boundary result consumes the reservation')
    expect(fixture(s,'pay',{'id':oid})['mutating_calls']==0,'Paid order cannot submit another charge')
    expect(int(catalog_snapshot(s)['product_variants'][0]['stock'])==start-2,'Payment acceptance does not deduct reserved stock again')
    expect('bag is empty' in client.request('shop/cart.php')[1],'Purchased quantity is removed from the corresponding private bag')
    expect(int(order(s,oid)['receipt_queued'])==1,'Paid order has durable deduplicated receipt notification state')
    owner='admin/orders/view.php?id='+str(oid)
    revision=order(s,oid)['revision']
    s.client.form(owner,{'action':'fulfill','fulfillment':'shipped','carrier':'Fixture Carrier','tracking':'SYNTHETIC-TRACK-001','revision':str(revision)})
    expect(order(s,oid)['fulfillment_state']=='shipped','Owner records shipment separately from payment acceptance')
    expect('SYNTHETIC-TRACK-001' in client.request('shop/order.php?reference='+o['reference'])[1],'Shipment tracking appears on the authorized customer receipt')
    s.client.form(owner,{'action':'fulfill','fulfillment':'completed','carrier':'Wrong','tracking':'Wrong','revision':str(revision)})
    expect(order(s,oid)['tracking_number']=='SYNTHETIC-TRACK-001','Stale fulfillment revision cannot overwrite a newer update')
    link=fixture(s,'private-link',{'id':oid});guest=Client(s.base)
    result=guest.request('shop/order.php?reference='+link['reference']+'&access='+link['access'])
    expect(result[0]==200 and 'access=' not in result[3],'Valid private guest receipt token grants access then is removed from the URL')
    expect('no-store' in result[2].get('Cache-Control','') and result[2].get('Referrer-Policy')=='no-referrer','Private receipt forbids caching and outgoing referrer leakage')
    return oid


def failure_and_recovery(s,vid):
    stock=lambda:int(catalog_snapshot(s)['product_variants'][0]['stock'])
    before=stock();c,declined,_,_=prepare(s,vid)
    result=fixture(s,'pay',{'id':declined,'response':'declined'})
    expect(result['mutating_calls']==1 and order(s,declined)['payment_state']=='declined' and stock()==before,'Definite decline releases its reservation once')
    expect(fixture(s,'pay',{'id':declined})['mutating_calls']==0 and stock()==before,'Declined order replay cannot charge again or release stock twice')
    before=stock();c,expired,_,_=prepare(s,vid);fixture(s,'expire',{'id':expired});fixture(s,'maintenance')
    expect(order(s,expired)['payment_state']=='expired' and stock()==before,'Expired unsubmitted checkout releases inventory')
    fixture(s,'maintenance');expect(stock()==before,'Repeated expiration maintenance is idempotent')
    c,uncertain,_,_=prepare(s,vid);fixture(s,'pay',{'id':uncertain,'response':'lost'});held_stock=stock()
    fixture(s,'expire',{'id':uncertain});fixture(s,'maintenance')
    expect(order(s,uncertain)['payment_state']=='uncertain' and stock()==held_stock,'Network uncertainty retains stock and is never auto-expired')
    expect(fixture(s,'pay',{'id':uncertain})['mutating_calls']==0,'Uncertain charge cannot be automatically resubmitted')
    tx='7000000000'+str(uncertain)
    body=notification(s,uncertain,tx)
    expect(post_webhook(s,body,False)[0]==401 and snapshot(s)['payment_webhooks']==[],'Invalid webhook signature cannot enter the durable inbox')
    expect(post_webhook(s,body)[0]==200,'Valid raw-body HMAC notification is durably accepted')
    expect(order(s,uncertain)['payment_state']=='uncertain','Signed notification alone is not treated as current payment proof')
    post_webhook(s,body)
    expect(len(snapshot(s)['payment_webhooks'])==1,'Repeated notification delivery is deduplicated')
    changed=json.loads(json.dumps(body));changed['payload']['authAmount']=999
    expect(post_webhook(s,changed)[0]==409,'Same notification ID with a different signed body cannot overwrite the inbox')
    result=fixture(s,'process-webhooks',{'id':uncertain,'transaction_id':tx})
    expect(result['processed']==1 and order(s,uncertain)['payment_state']=='paid','Signed request correlation plus matching provider reporting resolves an uncertain charge')
    expect(stock()==held_stock,'Reporting resolution consumes the same held inventory without a second deduction')
    # Every amount in the webhook deliberately claimed 0.01; matching comes from
    # authenticated reporting, not that stale or attacker-selected snapshot.
    expect(int(order(s,uncertain)['total_cents'])!=1,'Webhook amount never replaces the stored server-calculated total')
    c,wrong,_,_=prepare(s,vid);fixture(s,'pay',{'id':wrong,'response':'lost'})
    result=fixture(s,'reconcile',{'id':wrong,'transaction_id':'7000000000'+str(wrong),'amount':'0.01'})
    expect(result.get('ok') is False and order(s,wrong)['payment_state']=='uncertain','Reporting amount mismatch cannot mark an order paid')
    result=fixture(s,'reconcile',{'id':wrong,'transaction_id':'7000000000'+str(wrong),'transaction_type':'refundTransaction'})
    expect(result.get('ok') is False and order(s,wrong)['payment_state']=='uncertain','Wrong transaction type cannot satisfy a merchandise charge')
    c,receipt_failed,_,_=prepare(s,vid);result=fixture(s,'break-receipt',{'id':receipt_failed})
    expect(result['mutating_calls']==1 and order(s,receipt_failed)['payment_state']=='processing','Database failure after gateway acceptance retains the durable in-flight state')
    expect(fixture(s,'pay',{'id':receipt_failed})['mutating_calls']==0,'Failed accepted-payment receipt does not trigger a second charge')
    fixture(s,'interrupt',{'id':receipt_failed});fixture(s,'maintenance')
    expect(order(s,receipt_failed)['payment_state']=='uncertain','Abandoned payment process is explicitly flagged uncertain')
    fixture(s,'reconcile',{'id':receipt_failed,'transaction_id':'7000000000'+str(receipt_failed)})
    expect(order(s,receipt_failed)['payment_state']=='paid','Owner-pinned authenticated reporting can recover an accepted payment with a lost receipt')
    c,queue_fail,_,_=prepare(s,vid);fixture(s,'queue-break')
    try: fixture(s,'pay',{'id':queue_fail})
    finally: fixture(s,'queue-restore')
    expect(order(s,queue_fail)['payment_state']=='paid' and int(order(s,queue_fail)['receipt_queued'])==0,'Receipt-mail failure cannot undo or repeat an accepted payment')
    fixture(s,'maintenance')
    expect(int(order(s,queue_fail)['receipt_queued'])==1,'Maintenance recovers pending order receipt creation')
    c,reviewed,_,_=prepare(s,vid);fixture(s,'pay',{'id':reviewed,'response':'held'});fixture(s,'expire',{'id':reviewed});fixture(s,'maintenance')
    expect(order(s,reviewed)['payment_state']=='held' and order(s,reviewed)['reservation_state']=='held','Fraud-held payment is neither fulfilled nor auto-expired')
    s.client.form('admin/orders/view.php?id='+str(reviewed),{'action':'fulfill','fulfillment':'shipped','carrier':'Fixture','tracking':'X','revision':str(order(s,reviewed)['revision'])})
    expect(order(s,reviewed)['fulfillment_state']=='unfulfilled','Owner cannot fulfill an unverified held payment')


def reversals(s,vid):
    stock=lambda:int(catalog_snapshot(s)['product_variants'][0]['stock'])
    c,refund,_,_=prepare(s,vid);fixture(s,'pay',{'id':refund});before=stock()
    result=fixture(s,'reverse',{'id':refund,'kind':'refund','provider_status':'capturedPendingSettlement'})
    expect(result.get('ok') is False and result['mutating_calls']==0,'Refund requires verified settlement instead of guessing transaction state')
    result=fixture(s,'reverse',{'id':refund,'kind':'refund','provider_status':'settledSuccessfully'})
    expect(result['mutating_calls']==1 and order(s,refund)['payment_state']=='refunded','Full refund uses original transaction and last four, not stored card data')
    expect(stock()==before,'Refund does not falsely restock unreturned merchandise')
    expect(fixture(s,'reverse',{'id':refund,'kind':'refund','provider_status':'settledSuccessfully'})['mutating_calls']==0,'Repeated refund action cannot submit a second reversal')
    fixture(s,'reconcile',{'id':refund,'transaction_id':order(s,refund)['transaction_id']})
    expect(order(s,refund)['payment_state']=='refunded','Late original-payment report cannot regress a refunded order to paid')
    c,voided,_,_=prepare(s,vid);fixture(s,'pay',{'id':voided})
    result=fixture(s,'reverse',{'id':voided,'kind':'void','provider_status':'capturedPendingSettlement'})
    expect(result['mutating_calls']==1 and order(s,voided)['payment_state']=='voided','Unsettled payment supports a single verified void')
    c,lost,_,_=prepare(s,vid);fixture(s,'pay',{'id':lost})
    fixture(s,'reverse',{'id':lost,'kind':'refund','provider_status':'settledSuccessfully','response':'lost'})
    expect(order(s,lost)['payment_state']=='reversal_uncertain','Lost refund response does not claim a successful refund')
    expect(fixture(s,'reverse',{'id':lost,'kind':'refund','provider_status':'settledSuccessfully'})['mutating_calls']==0,'Uncertain refund cannot be blindly resubmitted')
    result=fixture(s,'refund-report',{'id':lost,'transaction_id':'8000000000'+str(lost),'refund_reporting':True})
    expect(order(s,lost)['payment_state']=='refunded','Verified full refund reporting resolves an uncertain reversal')
    return refund


def reservation_race(s,pid,vid):
    current=int(catalog_snapshot(s)['product_variants'][0]['stock']);adjust(s,pid,vid,1-current)
    browsers=[bag(s,vid) for _ in range(2)]
    def request(client):
        cookie=next(c.value for c in client.cookies if c.name.startswith('sg_cart_'))
        return fixture(s,'prepare',{'cart_cookie':cookie,'checkout':checkout_values()})
    with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
        results=list(pool.map(request,browsers))
    expect(sum(bool(r.get('ok')) for r in results)==1,'Two real database transactions cannot reserve the same final unit')
    expect(int(catalog_snapshot(s)['product_variants'][0]['stock'])==0,'Concurrent checkout does not oversell or produce negative stock')
    winner=next(r for r in results if r.get('ok'))
    s.client.form('admin/orders/view.php?id='+str(winner['id']),{'action':'cancel'})
    expect(int(catalog_snapshot(s)['product_variants'][0]['stock'])==1,'Cancelling the winning reservation returns one unit exactly')


def customer_orders(s,pid,vid):
    adjust(s,pid,vid,20)
    enable_fixture_smtp(s)
    s.client.form('admin/shop/settings.php',{'enabled':'1','accounts_enabled':'1','intro':'Synthetic store'})
    customer,password=signup(s,'buyer@example.test');customer.form('account/login.php',{'email':'buyer@example.test','password':password})
    c,owned,_,_=prepare(s,vid,client=customer);fixture(s,'pay',{'id':owned})
    expect(order(s,owned)['customer_id'] is not None and order(s,owned)['reference'] in customer.request('account/')[1],'Signed-in buyer sees their actual order history')
    outsider=Client(s.base)
    expect(outsider.request('shop/order.php?reference='+order(s,owned)['reference'])[0]==404,'Another browser cannot open customer-owned order history')
    c,guest,values,_=prepare(s,vid)
    expect(order(s,guest)['customer_id'] is None,'Guest orders are not attached by an unverified email match')
    expect(order(s,guest)['reference'] not in customer.request('account/')[1],'Customer history excludes unrelated guest receipts')


def upgrade(s):
    migration=s.root/'database/migrations/005.sql';body=migration.read_bytes();migration.unlink()
    s.claim();s.client.form('install/',s.input());s.login()
    s.client.form('admin/settings.php',{'site_name':'Preserved payment baseline','tagline':'Preserved','welcome':'Preserved'})
    migration.write_bytes(body)
    expect(s.client.request('admin/orders/')[3].endswith('/admin/updates.php'),'Existing .7 install requests the additive payment schema')
    expect(Client(s.base).request('shop/checkout.php')[0]==503,'Unmigrated checkout fails closed')
    s.client.form('admin/updates.php',{})
    expect(s.database('count')['users']==1 and 'Preserved payment baseline' in Client(s.base).request('')[1],'Payment migration preserves owner and original content')
    expect(snapshot(s)['orders']==[],'Payment migration creates no fabricated sales')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':
        raise RuntimeError('Non-root disposable database environment required.')
    os.environ['SPIRIT_DISABLE_PAYMENT_NETWORK']='1'
    with tempfile.TemporaryDirectory(prefix='spirit-payments-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        for folder in ['', 'spirit']:
            with site(base/('root' if not folder else 'nested'),archive,code,folder) as s:
                s.claim();s.client.form('install/',s.input());s.login()
                expect('Checkout is not open yet' in Client(s.base).request('shop/checkout.php')[1],'Default installation exposes no active payment form')
                pid,vid,_=seed_catalog(s);adjust(s,pid,vid,200);configure(s)
                for route in ['admin/orders/','admin/orders/payments.php','admin/orders/policy.php','admin/orders/webhooks.php']:
                    expect(Client(s.base).request(route)[3].endswith('/admin/login.php'),'Anonymous order administration denied: '+route)
                expect(Client(s.base).request('payments/sandbox.php')[0]==405 and Client(s.base).request('tasks/payments.php')[0]==404,'Webhook is POST-only and maintenance is CLI-only')
                money_and_privacy(s,pid,vid);failure_and_recovery(s,vid);reversals(s,vid);reservation_race(s,pid,vid);customer_orders(s,pid,vid)
                run_payment_task(s)
                s.database('editor');expect(s.client.request('admin/orders/')[0]==403,'Editor cannot inherit payment or customer order privileges');s.database('owner')
        with site(base/'upgrade',archive,code,'band') as s:upgrade(s)
    print('Payment SQL/HTTP, concurrency, privacy and recovery checks passed with a synthetic gateway boundary; no provider transaction executed.',flush=True)

if __name__=='__main__':main()
