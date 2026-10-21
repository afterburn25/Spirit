<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class StoreAdmin
{
    public static function render(string $view): void
    {
        $user=ContentAdmin::boot(in_array($view,['settings','customers'],true)?'owner':'store.manage');$actor=(int)$user['id'];$db=Database::connect();$error=null;
        $titles=['products'=>'Merchandise catalog','edit'=>'Product editor','variants'=>'Variants & inventory','categories'=>'Merchandise categories','settings'=>'Store settings','customers'=>'Customer accounts'];
        if(!isset($titles[$view])){throw new RuntimeException('Unknown store administration page.');}
        $id=Input::integer($_GET,'id',0,PHP_INT_MAX,0);$product=in_array($view,['edit','variants'],true)&&$id?Catalog::product($id):null;
        if(($view==='variants'||($view==='edit'&&$id))&&!$product){http_response_code(404);exit('Product not found.');}
        if($_SERVER['REQUEST_METHOD']==='POST'){
            Security::checkPost();
            try{
                if($view==='edit'){$saved=Catalog::saveProduct($id,$_POST,$actor);flash('Product saved. Private drafts do not change the published copy.');go('admin/shop/edit.php?id='.$saved);}
                if($view==='variants'){
                    if(post('action')==='stock'){Catalog::adjustStock(Input::integer($_POST,'variant_id',1,PHP_INT_MAX),$_POST,$actor);}
                    else{Catalog::saveVariant($id,Input::integer($_POST,'variant_id',0,PHP_INT_MAX,0),$_POST,$actor);}flash('Variant / inventory change saved.');go('admin/shop/variants.php?id='.$id);
                }
                if($view==='categories'){Catalog::saveCategory(Input::integer($_POST,'id',0,PHP_INT_MAX,0),$_POST,$actor);flash('Category saved. Hidden categories hide their public products.');go('admin/shop/categories.php');}
                if($view==='settings'){
                    $options=['enabled'=>post('enabled')==='1','accounts_enabled'=>post('accounts_enabled')==='1','intro'=>Input::text($_POST,'intro',1000,true)];
                    if($options['accounts_enabled']&&!Communications::ready()){throw new RuntimeException('Configure and test email delivery before enabling account registration and recovery.');}
                    ConfigStore::write('store',array_merge(Catalog::options(),$options),$actor);flash('Store settings saved. Payment enablement is managed separately under Orders & payments.');go('admin/shop/settings.php');
                }
                if($view==='customers'){
                    $customer=Input::integer($_POST,'customer_id',1,PHP_INT_MAX);$status=Input::choice($_POST,'status',['active','suspended'],'');$db->beginTransaction();
                    try{
                        $q=$db->prepare('SELECT verified_at FROM '.Database::table('customers').' WHERE id=? FOR UPDATE');$q->execute([$customer]);if(!$q->fetchColumn()){throw new RuntimeException('Only verified customer accounts can be enabled or suspended.');}
                        $q=$db->prepare('UPDATE '.Database::table('customers').' SET status=?,session_version=session_version+1 WHERE id=?');$q->execute([$status,$customer]);
                        $q=$db->prepare('UPDATE '.Database::table('customer_tokens').' SET used_at=UTC_TIMESTAMP() WHERE customer_id=? AND used_at IS NULL');$q->execute([$customer]);Database::audit($db,$actor,'customer.status.updated');$db->commit();
                    }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
                    flash('Customer access updated. Existing sessions were revoked.');go('admin/shop/customers.php');
                }
            }catch(InvalidArgumentException|RuntimeException $e){$error=Storefront::error($e);}
        }
        View::start($titles[$view],'admin',match($view){'categories'=>'store-categories','customers'=>'customers','settings'=>'store-settings',default=>'store'},$user);
        require Paths::root().'/app/views/admin/store.php';View::end();
    }
    public static function field(string $name,string $label,mixed $value='',string $type='text',int $max=180): void
    {
        echo '<label class="field-stack">'.e($label);
        if($type==='textarea'){echo '<textarea name="'.e($name).'" maxlength="'.$max.'" rows="5">'.e($value).'</textarea>';}
        else{echo '<input type="'.e($type).'" name="'.e($name).'" value="'.e($value).'" maxlength="'.$max.'">';}echo '</label>';
    }
}
