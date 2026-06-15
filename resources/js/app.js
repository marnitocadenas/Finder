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

function updateNotificationStatCards(){
    const statCards=document.querySelectorAll('.notification-stat-card');
    if(statCards.length<3)return;
    const visibleCards=document.querySelectorAll('.notification-card[data-notification-id]');
    const total=visibleCards.length;
    const unread=visibleCards.length-document.querySelectorAll('.notification-card[data-notification-id]:not(.is-unread)').length;
    const read=total-unread;
    const allStrong=statCards[0].querySelector('strong');
    const unreadStrong=statCards[1].querySelector('strong');
    const readStrong=statCards[2].querySelector('strong');
    if(allStrong)allStrong.textContent=total;
    if(unreadStrong)unreadStrong.textContent=unread;
    if(readStrong)readStrong.textContent=read;
}

function markNotificationItemAsRead(item){
    item.classList.remove('is-unread');
    const statusEl=item.querySelector('[data-notification-status]');
    if(statusEl){
        statusEl.textContent='Read';
        statusEl.classList.add('text-success');
    }
}

// Mark individual notification as read and navigate without adding history entry
// This ensures the browser back button returns to the Notifications inbox
document.addEventListener('click',(event)=>{
    const item=event.target.closest('.notification-card[data-notification-id]');
    if(!item)return;
    const link=event.target.closest('.notification-card-body');
    if(!link)return;

    const href=link.getAttribute('href');
    if(!href||href==='#')return;

    // Prevent normal navigation
    event.preventDefault();

    const id=item.dataset.notificationId;
    const csrfMeta=document.querySelector('meta[name="csrf-token"]');

    // Mark as read via AJAX (fire-and-forget)
    if(id&&csrfMeta&&item.classList.contains('is-unread')){
        fetch('/notifications/'+id+'/mark-read',{
            method:'POST',
            headers:{'X-CSRF-TOKEN':csrfMeta.content,'X-Requested-With':'XMLHttpRequest'}
        }).then(r=>r.json()).then(data=>{
            if(data.success){
                item.classList.remove('is-unread');
                const statusEl=item.querySelector('[data-notification-status]');
                if(statusEl){statusEl.textContent='Read';statusEl.classList.add('text-success');}
                refreshNotificationCount();
                updateNotificationStatCards();
            }
        }).catch(()=>{});
    }

    // Load the target page and replace the current history entry (notifications page)
    // so that the browser back button skips the inbox and returns to the previous module
    fetch(href,{credentials:'same-origin'}).then(r=>{
        if(!r.ok)throw new Error('Navigation failed');
        return r.text();
    }).then(html=>{
        // Replace current history entry instead of pushing a new one
        window.history.replaceState(null,'',href);
        // Render the fetched page
        document.open();
        document.write(html);
        document.close();
    }).catch(()=>{
        // Fallback: normal navigation if fetch fails
        window.location.href=href;
    });
});
setInterval(refreshNotificationCount,30000);
refreshNotificationCount();

// Handle "Mark All Read" form via AJAX on the notifications page
(function(){
    const form=document.getElementById('mark-all-read-form');
    if(!form)return;
    const btn=form.querySelector('button[type="submit"]');
    form.addEventListener('submit',function(e){
        e.preventDefault();
        const csrfMeta=document.querySelector('meta[name="csrf-token"]');
        if(!csrfMeta)return;
        const originalHTML=btn?btn.innerHTML:'';
        if(btn){btn.innerHTML='<i class="fa-solid fa-circle-notch fa-spin me-1"></i>Working';btn.disabled=true;}
        fetch(form.action,{
            method:'POST',
            headers:{'X-CSRF-TOKEN':csrfMeta.content,'X-Requested-With':'XMLHttpRequest'},
            body:new FormData(form)
        }).then(r=>r.json()).then(data=>{
            if(data.success){
                document.querySelectorAll('.notification-card.is-unread').forEach(row=>{
                    row.classList.remove('is-unread');
                    const statusEl=row.querySelector('[data-notification-status]');
                    if(statusEl){statusEl.textContent='Read';statusEl.classList.add('text-success');}
                });
                refreshNotificationCount();
                updateNotificationStatCards();
            }
        }).catch(()=>{}).finally(()=>{
            if(btn){btn.innerHTML=originalHTML;btn.disabled=false;}
        });
    });
})();

// --- Notification delete system (individual + bulk) ---
function getNotificationCheckboxes(){return Array.from(document.querySelectorAll('[data-notification-checkbox]'));}
function updateBulkDeleteUI(){
    const checked=getNotificationCheckboxes().filter(cb=>cb.checked);
    const btn=document.getElementById('notification-bulk-delete-btn');
    const countEl=document.getElementById('notification-bulk-count');
    const selectAll=document.getElementById('notification-select-all');
    if(!btn)return;
    const total=getNotificationCheckboxes().length;
    if(countEl)countEl.textContent=checked.length;
    btn.classList.toggle('d-none',checked.length===0);
    if(selectAll)selectAll.checked=total>0&&checked.length===total;
}

// Select All checkbox
(function(){
    const selectAll=document.querySelector('[data-notification-select-all]');
    if(!selectAll)return;
    selectAll.addEventListener('change',function(){
        getNotificationCheckboxes().forEach(cb=>{cb.checked=selectAll.checked;});
        updateBulkDeleteUI();
    });
})();

// Individual checkbox change
(function(){
    document.addEventListener('change',function(e){
        if(!e.target.matches('[data-notification-checkbox]'))return;
        updateBulkDeleteUI();
    });
})();

// Individual delete button (handled by confirm modal via data-confirm-submit)

// Bulk delete: populate IDs before the confirm modal triggers form submit
(function(){
    const btn=document.getElementById('notification-bulk-delete-btn');
    if(!btn)return;
    btn.addEventListener('click',function(){
        const ids=getNotificationCheckboxes().filter(cb=>cb.checked).map(cb=>cb.value);
        const idsField=document.getElementById('notification-bulk-ids');
        if(idsField)idsField.value=ids.join(',');
    });
})();

// Bulk delete: also handle AJAX for in-page removal after form submit
(function(){
    const form=document.getElementById('notification-bulk-delete-form');
    if(!form)return;
    form.addEventListener('submit',function(e){
        e.preventDefault();
        const ids=getNotificationCheckboxes().filter(cb=>cb.checked).map(cb=>cb.value);
        if(ids.length===0)return;
        const idsField=document.getElementById('notification-bulk-ids');
        if(idsField)idsField.value=ids.join(',');
        const csrfMeta=document.querySelector('meta[name="csrf-token"]');
        if(!csrfMeta)return;
        fetch(form.action,{
            method:'POST',
            headers:{'X-CSRF-TOKEN':csrfMeta.content,'X-Requested-With':'XMLHttpRequest'},
            body:new FormData(form)
        }).then(r=>r.json()).then(data=>{
            if(data.success){
                ids.forEach(id=>{
                    const row=document.querySelector('.notification-card[data-notification-id="'+id+'"]');
                    if(row)row.remove();
                });
                refreshNotificationCount();
                updateNotificationStatCards();
                updateBulkDeleteUI();
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
    if(event.target.hasAttribute('data-ajax'))return;
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

