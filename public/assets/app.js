const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

const api = {
  async list(params = {}) {
    const q = new URLSearchParams(params).toString();
    const res = await fetch(`/api/todos${q ? `?${q}` : ''}`);
    return res.json();
  },
  async create(data) {
    const res = await fetch('/api/todos', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    return res.json();
  },
  async update(id, data) {
    const res = await fetch(`/api/todos/${id}`, { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    return res.json();
  },
  async remove(id) {
    const res = await fetch(`/api/todos/${id}`, { method: 'DELETE' });
    return res.json();
  }
};

function renderItem(t) {
  const li = document.createElement('li');
  li.className = `todo ${t.status === 'done' ? 'done' : ''}`;
  li.dataset.id = t.id;
  li.innerHTML = `
    <input type="checkbox" class="toggle" ${t.status === 'done' ? 'checked' : ''}>
    <div>
      <div class="title" contenteditable="true">${escapeHtml(t.title)}</div>
      <div class="tags">${(t.tags || '').split(',').filter(Boolean).map(x => '#' + x.trim()).join(' ')}</div>
      <div class="meta">${t.due_at ? 'Due ' + new Date(t.due_at).toLocaleDateString() : ''} • Created ${new Date(t.created_at).toLocaleString()}</div>
    </div>
    <div class="actions">
      <button class="archive">Archive</button>
      <button class="delete">Delete</button>
    </div>
  `;
  return li;
}

function escapeHtml(s) {
  return s.replace(/[&<>"]+/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
}

async function refresh() {
  const params = {};
  const q = $('#search').value.trim();
  const status = $('#statusFilter').value;
  if (q) params.q = q; if (status) params.status = status;
  const items = await api.list(params);
  ['open','done','archived'].forEach(s => $(`#list-${s}`).innerHTML = '');
  items.forEach(t => {
    const li = renderItem(t);
    $(`#list-${t.status}`).appendChild(li);
  });
}

async function boot() {
  // Create
  $('#create').addEventListener('click', async () => {
    const title = $('#title').value.trim();
    if (!title) return;
    const tags = $('#tags').value.trim();
    const due = $('#due').value || null;
    await api.create({ title, tags, due_at: due });
    $('#title').value = '';
    refresh();
  });
  $('#addQuick').addEventListener('click', () => $('#title').focus());
  $('#focusMode').addEventListener('click', () => document.body.classList.toggle('focus'));
  $('#exportBtn').addEventListener('click', async () => {
    const data = await api.list({});
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `todos-export-${new Date().toISOString().slice(0,10)}.json`;
    a.click();
  });

  $('#search').addEventListener('keydown', e => { if (e.key === 'Escape') { e.target.value = ''; refresh(); }});

  // Delegated events
  document.body.addEventListener('keydown', e => {
    if (e.key === '/') { e.preventDefault(); $('#search').focus(); }
    if (e.key.toLowerCase() === 'n') { $('#title').focus(); }
  });

  document.body.addEventListener('click', async e => {
    const li = e.target.closest('.todo');
    if (!li) return;
    const id = li.dataset.id;
    if (e.target.matches('.toggle')) {
      const done = e.target.checked;
      await api.update(id, { status: done ? 'done' : 'open' });
      refresh();
    }
    if (e.target.matches('.archive')) {
      await api.update(id, { status: 'archived' });
      refresh();
    }
    if (e.target.matches('.delete')) {
      if (confirm('Delete?')) { await api.remove(id); refresh(); }
    }
  });

  document.body.addEventListener('focusout', async e => {
    if (e.target.matches('.title')) {
      const li = e.target.closest('.todo');
      const id = li.dataset.id;
      const title = e.target.textContent.trim();
      if (title) await api.update(id, { title });
    }
  });

  await refresh();
}

boot();
