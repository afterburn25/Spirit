/* Owner dashboard enhancement. Reports and forms remain usable without JavaScript. */
(()=>{'use strict';
 const panel=document.querySelector('[data-live-url]');if(!panel)return;
 const button=panel.querySelector('[data-live-toggle]'),status=panel.querySelector('[data-live-status]'),rows=panel.querySelector('[data-live-rows]'),count=document.querySelector('[data-live-count]');
 let paused=false,busy=false,stopped=false;
 button.hidden=false;
 const duration=n=>{n=Math.max(0,Math.round(Number(n)||0));return n<60?n+'s':n<3600?Math.floor(n/60)+'m '+n%60+'s':Math.floor(n/3600)+'h '+Math.floor(n%3600/60)+'m';};
 const cell=(tr,text)=>{const td=document.createElement('td');td.textContent=text;tr.append(td);return td;};
 const refresh=async()=>{
  if(paused||busy||stopped||document.visibilityState!=='visible')return;busy=true;
  try{
   const response=await fetch(panel.dataset.liveUrl,{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});
   if(!response.ok||response.redirected)throw new Error('unavailable');
   const data=await response.json();if(!data.available||!Array.isArray(data.visitors))throw new Error('unavailable');
   const body=document.createDocumentFragment();
   for(const visitor of data.visitors.slice(0,100)){
    const tr=document.createElement('tr'),identity=cell(tr,'Visit '+visitor.id),dot=document.createElement('span');dot.className='analytics-live-dot';dot.setAttribute('aria-hidden','true');identity.prepend(dot);
    cell(tr,visitor.path||'/');cell(tr,[visitor.city,visitor.region,visitor.country].filter(value=>value&&value!=='Unknown').join(', ')||'Unknown');
    const device=cell(tr,visitor.device||'Unknown'),browser=document.createElement('small');browser.textContent=visitor.browser||'Unknown';device.append(browser);
    cell(tr,duration(visitor.active_seconds));cell(tr,String(visitor.last_seen||'').slice(11,19)+' UTC');body.append(tr);
   }
   if(!data.visitors.length){const tr=document.createElement('tr'),td=cell(tr,'No visitors active in the last minute.');td.colSpan=6;td.className='analytics-empty';body.append(tr);}
   rows.replaceChildren(body);count.textContent=new Intl.NumberFormat().format(Number(data.active_visitors)||0);
   status.textContent='Live · updated '+String(data.as_of||'')+' · refreshes every 15 seconds';
  }catch(error){status.textContent='Live update unavailable. The previous snapshot remains shown; retrying shortly.';}
  finally{busy=false;}
 };
 button.addEventListener('click',()=>{paused=!paused;button.textContent=paused?'Resume live updates':'Pause live updates';button.setAttribute('aria-pressed',String(paused));if(paused)status.textContent='Live updates paused. The last snapshot remains shown.';else refresh();});
 document.addEventListener('visibilitychange',()=>{if(document.visibilityState==='visible')refresh();});
 window.addEventListener('pagehide',()=>{stopped=true;});window.addEventListener('pageshow',()=>{stopped=false;refresh();});
 setInterval(refresh,15000);refresh();
})();
