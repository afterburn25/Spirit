/* First-party visible-page timing. No DOM/form inspection or cross-site requests. */
(()=>{'use strict';let page=null;
 const allowed=()=>navigator.globalPrivacyControl!==true&&navigator.doNotTrack!=='1'&&window.doNotTrack!=='1';
 const accrue=()=>{if(!page)return;const now=performance.now();if(page.visible)page.seconds=Math.min(14400,page.seconds+Math.max(0,now-page.at)/1000);page.at=now;};
 const send=(final=false)=>{if(!page||!allowed())return;accrue();const body=JSON.stringify({token:page.token,active_seconds:Math.floor(page.seconds),visible:page.visible});if(final&&navigator.sendBeacon&&navigator.sendBeacon(page.endpoint,new Blob([body],{type:'application/json'})))return;fetch(page.endpoint,{method:'POST',body,headers:{'Content-Type':'application/json'},credentials:'same-origin',keepalive:final,cache:'no-store'}).catch(()=>{});};
 const start=()=>{if(page){accrue();page.visible=false;send(true);}page=null;if(!allowed())return;const meta=document.querySelector('#site-content [data-analytics]');if(!meta||!meta.dataset.token||!meta.dataset.endpoint)return;let endpoint;try{endpoint=new URL(meta.dataset.endpoint,location.href);if(endpoint.origin!==location.origin)return;}catch{return;}page={token:meta.dataset.token,endpoint:endpoint.href,seconds:0,at:performance.now(),visible:document.visibilityState==='visible'};send();};
 document.addEventListener('visibilitychange',()=>{if(!page)return;accrue();page.visible=document.visibilityState==='visible';page.at=performance.now();send(!page.visible);});
 window.addEventListener('pagehide',()=>{if(!page)return;accrue();page.visible=false;send(true);});
 window.addEventListener('pageshow',event=>{if(event.persisted&&page){page.visible=document.visibilityState==='visible';page.at=performance.now();send();}});
 document.addEventListener('spirit:navigated',start);setInterval(()=>{if(page&&page.visible)send();},15000);start();
})();
