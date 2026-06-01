import './bootstrap';
import '../css/app.css';

setTimeout(()=>document.querySelectorAll('.auto-dismiss').forEach(el=>el.remove()),4000);

async function refreshNotificationCount(){
  const badge = document.querySelector('[data-notification-count]');
  const sr = document.getElementById('notification-sr');
  if(!badge) return;
  try{
    const response = await fetch('/notifications/count', { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
    const data = await response.json();
    badge.textContent = data.count;
    badge.classList.toggle('d-none', data.count < 1);
    if(sr) sr.textContent = `You have ${data.count} unread notifications`;
  }catch(e){ }
}

setInterval(refreshNotificationCount,30000);
refreshNotificationCount();

// Mobile sidebar toggle
const mobileToggle = document.getElementById('mobile-sidebar-toggle');
if(mobileToggle){
  mobileToggle.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-open');
  });
  // Close on escape
  document.addEventListener('keydown', (e) => {
    if(e.key === 'Escape') document.body.classList.remove('sidebar-open');
  });
  // Close when clicking outside sidebar
  document.addEventListener('click', (e) => {
    if(!e.target.closest('.sidebar') && !e.target.closest('#mobile-sidebar-toggle')){
      document.body.classList.remove('sidebar-open');
    }
  });
}

