<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

final class Storefront
{
    public static function boot(): void
    {
        if(!Paths::installed()){go('install/');}
        if(!Migrations::ready()){PublicSite::render('shop');exit;}
        header('Cache-Control: private, no-store');
    }
    public static function error(\Throwable $e): string
    {
        return $e instanceof PDOException?'The operation could not finish. Please try again.':$e->getMessage();
    }
    public static function render(string $view,array $context=[]): void
    {
        self::boot();$settings=Settings::all();$isPreview=isset($context['page_preview']);
        if($isPreview){Auth::requirePermission('content.manage');if($view!=='catalog'||($context['page_preview']['kind']??'')!=='page'||($context['page_preview']['slug']??'')!=='merch'){throw new InvalidArgumentException('Invalid section preview.');}header('X-Robots-Tag: noindex, nofollow');header('Referrer-Policy: no-referrer');}
        $pageContent=$view==='catalog'?($context['page_preview']??Content::bySlug('page','merch')):null;$metaRecord=$pageContent;
        $storeViews=['catalog','product','cart'];
        $active=in_array($view,$storeViews,true)?'shop':'account';$record=null;$canonical=null;
        $titles=['catalog'=>'Official merchandise','product'=>'Merchandise','cart'=>'Your bag','login'=>'Customer sign in','register'=>'Create your account','forgot'=>'Reset your password','complete'=>'Secure your account','account'=>'Your account','addresses'=>'Saved addresses','password'=>'Account security'];
        if(!isset($titles[$view])){throw new RuntimeException('Unknown store view.');}
        $title=$titles[$view];$description=$view==='catalog'?'Explore approved Spiritual Gorillaz merchandise, product photographs, available options and care information.':$settings['welcome'];$store=Catalog::options();$error=$context['error']??null;$customer=$context['customer']??null;
        if($view==='product'){$record=Catalog::bySlug(is_string($_GET['slug']??null)?$_GET['slug']:'');if(!$record){http_response_code(404);$title='Product unavailable';}else{$title=$record['data']['name'];}}
        if(!in_array($view,['catalog','product'],true)){header('X-Robots-Tag: noindex, nofollow');header('Referrer-Policy: no-referrer');}
        if(!$isPreview&&$store['enabled']&&$view==='product'&&$record){$canonical=Metadata::absolute(app_url('shop/product.php?slug='.rawurlencode($record['slug'])));$description=$record['data']['description'];$record['kind']='product';}
        elseif(!$isPreview&&$store['enabled']&&$view==='catalog'&&!isset($_GET['q'])&&!isset($_GET['category'])){$page=Input::integer($_GET,'page',1,1000000,1);$canonical=Metadata::absolute(app_url('shop/'.($page>1?'?page='.$page:'')));}
        $analyticsPage = !$isPreview && $store['enabled'] && ($view === 'catalog' || ($view === 'product' && $record))
            ? Analytics::beginPage() : null;
        require Paths::root().'/app/views/public/header.php';
        require Paths::root().'/app/views/public/'.(in_array($view,['catalog','product','cart'],true)?'store.php':'customer.php');
        require Paths::root().'/app/views/public/footer.php';
    }
    public static function cart(): void
    {
        self::boot();$error=null;
        if($_SERVER['REQUEST_METHOD']==='POST'){
            Security::checkPost();try{Cart::mutate($_POST);flash('Your bag was updated. Prices and availability are checked again when viewed.');go('shop/cart.php');}catch(InvalidArgumentException|RuntimeException $e){$error=self::error($e);}
        }
        self::render('cart',compact('error'));
    }
    public static function pager(array $listing,string $path,array $parameters=[]): void
    {
        if($listing['pages']<2){return;}
        echo '<nav class="pagination" aria-label="Result pages">';
        for($p=max(1,$listing['page']-2);$p<=min($listing['pages'],$listing['page']+2);$p++){
            echo '<a '.($p===$listing['page']?'aria-current="page"':'').' href="'.e(app_url($path.'?'.http_build_query(array_merge($parameters,['page'=>$p])))).'">'.$p.'</a>';
        }echo '</nav>';
    }
    public static function card(array $product): void
    {
        $data=$product['data'];$variants=$product['variants'];$prices=array_map(fn($v)=>(int)$v['price_cents'],$variants);$url=app_url('shop/product.php?slug='.rawurlencode($product['slug']));
        echo '<article class="merch-card"><a class="merch-art" href="'.e($url).'" aria-label="View product: '.e($data['name']).'">'.PublicSite::image((int)($data['image_ids'][0]??0),$data['name']).'</a><p class="eyebrow">OFFICIAL MERCH</p><h2><a href="'.e($url).'">'.e($data['name']).'</a></h2><p class="merch-price">'.($prices?'From '.e(Money::format(min($prices))):'Currently unavailable').'</p><a class="button wide" href="'.e($url).'">Choose options →</a></article>';
    }
}
