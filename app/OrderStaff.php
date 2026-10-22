<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;

/** Fulfillment-only view: no gateway keys, reporting operations or reversals. */
final class OrderStaff
{
    public static function render(string $view,array $user): void
    {
        $error=null;$db=Database::connect();$id=Input::integer($_GET,'id',0,PHP_INT_MAX,0);$order=$view==='view'?Orders::get($id):null;
        if($view==='view'&&!$order){http_response_code(404);exit('Order not found.');}
        if($_SERVER['REQUEST_METHOD']==='POST'){
            Security::checkPost();
            if($view!=='view'||post('action')!=='fulfill'||!Permissions::allows($user,'orders.fulfill')){http_response_code(403);exit('Only shipment updates are available to store managers.');}
            try{Orders::fulfill($id,$_POST,(int)$user['id']);flash('Fulfillment updated and customer notification queued.');go('admin/orders/view.php?id='.$id);}
            catch(InvalidArgumentException|RuntimeException $e){$error=Storefront::error($e);}
        }
        $pages=max(1,(int)ceil((int)$db->query('SELECT COUNT(*) FROM '.Database::table('orders'))->fetchColumn()/25));$page=max(1,min($pages,Input::integer($_GET,'page',1,PHP_INT_MAX,1)));
        $rows=$view==='orders'?$db->query('SELECT id,reference,buyer_email,environment,total_cents,payment_state,fulfillment_state FROM '.Database::table('orders').' ORDER BY id DESC LIMIT 25 OFFSET '.(($page-1)*25))->fetchAll():[];
        View::start('Order fulfillment','admin','orders',$user);require Paths::root().'/app/views/admin/order-staff.php';View::end();
    }
}
