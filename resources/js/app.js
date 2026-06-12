import './bootstrap';
import '../css/app.css';
setTimeout(()=>document.querySelectorAll('.auto-dismiss').forEach((el)=>el.remove()),4000);

// --- Notification auto-mark-as-read system ---
async function refreshNotificationCount(){
    const badge=document.querySelector('[data-notification-count]');
    if(!badge)return;
    try{
        const response=await fetch('/notifications/count',{headers:{'X-Requested-With':'XMLHttpRequest'}});
        const data=await response.json();
        badge.textContent=data.count;
        badge.classList.toggle('d-none',data.count<1);
    }catch(e){}
}

function updateNotificationStatCards(unreadCount){
    const statCards=document.querySelectorAll('.notification-stat-card');
    if(statCards.length<3)return;
    const allAlertsCard=statCards[0];
    const unreadCard=statCards[1];
    const readCard=statCards[2];
    const total=parseInt(allAlertsCard.querySelector('strong')?.textContent)||0;
    const newUnread=Math.max(0,unreadCount);
    const newRead=Math.max(0,total-newUnread);
    const unreadVal=allAlertsCard.querySelector('strong');
    const readVal=readCard.querySelector('strong');
    const unreadStrong=unreadCard.querySelector('strong');
    if(unreadStrong)unreadStrong.textContent=newUnread;
    if(readVal)readVal.textContent=newRead;
}

function markNotificationItemAsRead(item){
    item.classList.remove('is-unread');
    const statusEl=item.querySelector('[data-notification-status]');
    if(statusEl){
        statusEl.textContent='Read';
        statusEl.classList.add('text-success');
    }
}

// Auto-mark all visible unread notifications as read when the notifications page loads
(function(){
    const notifPage=document.querySelector('.notifications-module');
    if(!notifPage)return;
    const unreadItems=notifPage.querySelectorAll('.notification-item.is-unread');
    if(unreadItems.length===0)return;
    const ids=Array.from(unreadItems).map(el=>el.dataset.notificationId).filter(Boolean);
    if(ids.length===0)return;
    Promise.all(ids.map(id=>
        fetch('/notifications/'+id+'/mark-read',{
            method:'POST',
            headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'X-Requested-With':'XMLHttpRequest'}
        }).then(r=>r.json()).catch(()=>null)
    )).then(results=>{
        const lastResult=results.filter(Boolean).pop();
        if(lastResult){
            unreadItems.forEach(markNotificationItemAsRead);
            refreshNotificationCount();
            updateNotificationStatCards(lastResult.count);
        }
    });
})();

// Mark individual notification as read when clicked (works on all pages)
document.addEventListener('click',(event)=>{
    const item=event.target.closest('.notification-item[data-notification-id]');
    if(!item||!item.classList.contains('is-unread'))return;
    const id=item.dataset.notificationId;
    if(!id)return;
    const csrfMeta=document.querySelector('meta[name="csrf-token"]');
    if(!csrfMeta)return;
    fetch('/notifications/'+id+'/mark-read',{
        method:'POST',
        headers:{'X-CSRF-TOKEN':csrfMeta.content,'X-Requested-With':'XMLHttpRequest'}
    }).then(r=>r.json()).then(data=>{
        if(data.success){
            markNotificationItemAsRead(item);
            refreshNotificationCount();
            updateNotificationStatCards(data.count);
        }
    }).catch(()=>{});
});
setInterval(refreshNotificationCount,30000);
refreshNotificationCount();

// Handle "Mark All Read" form via AJAX on the notifications page
(function(){
    const form=document.getElementById('mark-all-read-form');
    if(!form)return;
    form.addEventListener('submit',function(e){
        e.preventDefault();
        const csrfMeta=document.querySelector('meta[name="csrf-token"]');
        if(!csrfMeta)return;
        fetch(form.action,{
            method:'POST',
            headers:{'X-CSRF-TOKEN':csrfMeta.content,'X-Requested-With':'XMLHttpRequest'},
            body:new FormData(form)
        }).then(r=>r.json()).then(data=>{
            if(data.success){
                document.querySelectorAll('.notification-item.is-unread').forEach(markNotificationItemAsRead);
                refreshNotificationCount();
                updateNotificationStatCards(0);
            }
        }).catch(()=>{});
    });
})();
document.addEventListener('change',(event)=>{
    if(!event.target.matches('[data-check-all]'))return;
    const table=event.target.closest('table');
    table?.querySelectorAll('tbody input[type="checkbox"]:not(:disabled)').forEach((checkbox)=>{checkbox.checked=event.target.checked;});
});
document.addEventListener('submit',(event)=>{
    const form=event.target;
    if(!form.classList.contains('bulk-toolbar'))return;
    if(!document.querySelector(`input[form="${form.id}"]:checked`)){
        event.preventDefault();
        alert('Select at least one record first.');
    }
});
document.addEventListener('click',(event)=>{
    const trigger=event.target.closest('[data-image-preview]');
    if(!trigger)return;
    const src=trigger.dataset.imagePreview;
    if(!src)return;
    const modalEl=document.getElementById('imagePreviewModal');
    const image=modalEl?.querySelector('[data-image-preview-target]');
    if(!modalEl||!image||!window.bootstrap)return;
    image.src=src;
    new bootstrap.Modal(modalEl).show();
});
document.addEventListener('change',(event)=>{
    const select=event.target.closest('[data-template-target]');
    if(!select||!select.value)return;
    const target=document.querySelector(select.dataset.templateTarget);
    if(target)target.value=select.value;
});
document.querySelectorAll('[data-character-counter]').forEach((field)=>{
    const target=document.querySelector(field.dataset.characterCounter);
    const update=()=>{if(target)target.textContent=field.value.length;};
    field.addEventListener('input',update);
    update();
});
document.addEventListener('click',(event)=>{
    const trigger=event.target.closest('[data-smooth-scroll]');
    if(!trigger)return;
    const href=trigger.getAttribute('href')||'';
    if(!href.startsWith('#'))return;
    const target=document.querySelector(href);
    if(!target)return;
    event.preventDefault();
    target.scrollIntoView({behavior:'smooth',block:'start'});
});
document.addEventListener('click',(event)=>{
    const trigger=event.target.closest('[data-password-toggle]');
    if(!trigger)return;
    const input=document.querySelector(trigger.dataset.passwordToggle);
    if(!input)return;
    const visible=input.type==='text';
    input.type=visible?'password':'text';
    trigger.setAttribute('aria-label',visible?'Show password':'Hide password');
    const icon=trigger.querySelector('i');
    icon?.classList.toggle('fa-eye',visible);
    icon?.classList.toggle('fa-eye-slash',!visible);
    input.focus();
});
const passwordRules=[
    ['length',(value)=>value.length>=8],
    ['upper',(value)=>/[A-Z]/.test(value)],
    ['lower',(value)=>/[a-z]/.test(value)],
    ['number',(value)=>/[0-9]/.test(value)],
    ['symbol',(value)=>/[^A-Za-z0-9]/.test(value)],
];
function updatePasswordMeter(field){
    const form=field.closest('form')||document;
    const meter=form.querySelector('[data-password-meter]');
    if(!meter)return;
    const passed=passwordRules.filter(([,test])=>test(field.value)).length;
    meter.dataset.score=String(passed);
    const bar=meter.querySelector('.auth-password-meter-bar span');
    if(bar)bar.style.width=`${(passed/passwordRules.length)*100}%`;
    passwordRules.forEach(([name,test])=>{
        const rule=meter.querySelector(`[data-rule="${name}"]`);
        if(rule)rule.classList.toggle('is-met',test(field.value));
    });
}
document.querySelectorAll('[data-password-strength]').forEach((field)=>{
    field.addEventListener('input',()=>updatePasswordMeter(field));
    updatePasswordMeter(field);
});
document.addEventListener('keyup',(event)=>{
    const field=event.target.closest('[data-caps-lock]');
    if(!field||typeof event.getModifierState!=='function')return;
    const warning=field.closest('.mb-3')?.querySelector('[data-caps-lock-warning]');
    if(warning)warning.classList.toggle('is-visible',event.getModifierState('CapsLock'));
});
document.addEventListener('blur',(event)=>{
    const field=event.target.closest?.('[data-caps-lock]');
    const warning=field?.closest('.mb-3')?.querySelector('[data-caps-lock-warning]');
    if(warning)warning.classList.remove('is-visible');
},true);
document.addEventListener('click',(event)=>{
    const trigger=event.target.closest('.student-detail-button');
    if(!trigger)return;
    const modalEl=document.getElementById('studentItemPreviewModal');
    if(!modalEl||!window.bootstrap)return;
    const image=modalEl.querySelector('[data-student-preview-modal-image]');
    const imageWrap=modalEl.querySelector('.student-preview-image');
    modalEl.querySelector('[data-student-preview-modal-title]').textContent=trigger.dataset.studentPreviewTitle||'Found Item';
    modalEl.querySelector('[data-student-preview-modal-category]').textContent=trigger.dataset.studentPreviewCategory||'-';
    modalEl.querySelector('[data-student-preview-modal-date]').textContent=trigger.dataset.studentPreviewDate||'-';
    modalEl.querySelector('[data-student-preview-modal-location]').textContent=trigger.dataset.studentPreviewLocation||'-';
    modalEl.querySelector('[data-student-preview-modal-description]').textContent=trigger.dataset.studentPreviewDescription||'-';
    modalEl.querySelector('[data-student-preview-modal-claim]').href=trigger.dataset.studentPreviewClaim||'#';
    if(image&&imageWrap){
        image.src=trigger.dataset.studentPreviewImage||'';
        imageWrap.classList.toggle('is-empty',!trigger.dataset.studentPreviewImage);
    }
    new bootstrap.Modal(modalEl).show();
});
document.addEventListener('submit',(event)=>{
    const button=event.target.querySelector('button[type="submit"]:not([disabled]), button:not([type]):not([disabled])');
    if(button){
        const label=button.dataset.loadingText||'Working';
        button.dataset.originalText=button.innerHTML;
        button.innerHTML=`<i class="fa-solid fa-circle-notch fa-spin me-1"></i>${label}`;
        button.disabled=true;
    }
});

// Mobile-only: keep fixed topbar offset in sync with its real height
(function(){
    function syncTopbarHeight(){
        if(window.innerWidth>767){
            document.documentElement.style.removeProperty('--mobile-topbar-h');
            return;
        }
        const topbar=document.querySelector('.topbar');
        if(topbar){
            document.documentElement.style.setProperty('--mobile-topbar-h',topbar.offsetHeight+'px');
        }
    }
    syncTopbarHeight();
    window.addEventListener('resize',syncTopbarHeight);
    if(window.ResizeObserver){
        const t=document.querySelector('.topbar');
        if(t)new ResizeObserver(syncTopbarHeight).observe(t);
    }
})();

// Responsive sidebar toggle (off-canvas hamburger menu)
(function(){
    const sidebar=document.querySelector('.sidebar');
    const toggle=document.querySelector('#sidebarToggle');
    const backdrop=document.querySelector('#sidebarBackdrop');
    if(!sidebar||!toggle)return;

    /* Inject a mobile-only close (X) button into the sidebar header */
    const header=sidebar.querySelector('.d-flex.align-items-center');
    let closeBtn=null;
    if(header){
        closeBtn=document.createElement('button');
        closeBtn.type='button';
        closeBtn.className='sidebar-close-btn';
        closeBtn.setAttribute('aria-label','Close menu');
        closeBtn.innerHTML='<i class="fa-solid fa-xmark"></i>';
        header.appendChild(closeBtn);
    }

    function openSidebar(){
        sidebar.classList.add('sidebar-open');
        if(backdrop)backdrop.classList.add('show');
        document.body.style.overflow='hidden';
    }
    function closeSidebar(){
        sidebar.classList.remove('sidebar-open');
        if(backdrop)backdrop.classList.remove('show');
        document.body.style.overflow='';
    }
    toggle.addEventListener('click',function(e){
        e.preventDefault();
        if(sidebar.classList.contains('sidebar-open')){
            closeSidebar();
        }else{
            openSidebar();
        }
    });
    if(closeBtn){
        closeBtn.addEventListener('click',closeSidebar);
    }
    if(backdrop){
        backdrop.addEventListener('click',closeSidebar);
    }
    document.addEventListener('keydown',function(e){
        if(e.key==='Escape'&&sidebar.classList.contains('sidebar-open')){
            closeSidebar();
        }
    });
})();

// Responsive table cards mode via overflow detection
(function(){
    if(!document.querySelector('[class*="-table-wrap"]'))return;
    function checkTableOverflow(){
        document.querySelectorAll('[class*="-table-wrap"]').forEach(function(wrapper){
            var table=wrapper.querySelector(':scope>table');
            if(!table)return;
            var wasCards=wrapper.classList.contains('table-cards-mode');
            if(wasCards)wrapper.classList.remove('table-cards-mode');
            if(table.scrollWidth>wrapper.clientWidth+1)wrapper.classList.add('table-cards-mode');
        });
    }
    checkTableOverflow();
    if(window.ResizeObserver){
        var ro=new ResizeObserver(function(){checkTableOverflow();});
        document.querySelectorAll('[class*="-table-wrap"]').forEach(function(w){ro.observe(w);});
    }
})();

// Asynchronous search handler for the landing page
async function performAsyncSearch(url) {
    try {
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) throw new Error('Search failed');

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Update search wrap (inputs, chips, clear button)
        const currentSearchWrap = document.querySelector('.landing-search-wrap');
        const newSearchWrap = doc.querySelector('.landing-search-wrap');
        if (currentSearchWrap && newSearchWrap) {
            currentSearchWrap.innerHTML = newSearchWrap.innerHTML;
        }

        // Update search results
        const currentResults = document.getElementById('recent-found');
        const newResults = doc.getElementById('recent-found');
        if (currentResults && newResults) {
            currentResults.innerHTML = newResults.innerHTML;
        }

        // Update the URL in the address bar
        window.history.pushState(null, '', url);
    } catch (error) {
        console.error(error);
    }
}

// Intercept search form submit
document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!form.classList.contains('landing-search')) return;

    event.preventDefault();

    const formData = new FormData(form);
    const params = new URLSearchParams(formData).toString();
    const action = form.getAttribute('action') || window.location.pathname;
    const url = `${action}?${params}`;

    await performAsyncSearch(url);
});

// Intercept category chips and clear button clicks
document.addEventListener('click', async (event) => {
    const chipLink = event.target.closest('.landing-category-chips a');
    const clearLink = event.target.closest('.landing-search a[href]');

    const link = chipLink || clearLink;
    if (!link) return;

    event.preventDefault();
    const url = link.getAttribute('href');
    await performAsyncSearch(url);
});

