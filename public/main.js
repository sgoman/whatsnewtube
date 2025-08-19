// --- LocalStorage helpers ---
function getGroups() {
    return JSON.parse(localStorage.getItem('groups') || '{}');
}
function saveGroups(groups) {
    localStorage.setItem('groups', JSON.stringify(groups));
}

// --- Collapsed state helpers ---
function getCollapsedGroups() {
    return JSON.parse(localStorage.getItem('collapsedGroups') || '{}');
}
function saveCollapsedGroups(obj) {
    localStorage.setItem('collapsedGroups', JSON.stringify(obj));
}

// Shorts filter helpers
function getShortsFilter() {
    return JSON.parse(localStorage.getItem('shortsFilter') || '{}');
}
function saveShortsFilter(obj) {
    localStorage.setItem('shortsFilter', JSON.stringify(obj));
}

// --- UI rendering ---
function renderGroups() {
    const groups = getGroups();
    const collapsedGroups = getCollapsedGroups();
    const shortsFilter = getShortsFilter();
    const groupsDiv = document.getElementById('groups');
    groupsDiv.innerHTML = '';
    Object.entries(groups).forEach(([group, channels], groupIdx, groupArr) => {
        const groupDiv = document.createElement('div');
        groupDiv.className = 'group';
        const isCollapsed = collapsedGroups[group];
        groupDiv.innerHTML = `
            <strong>${group}</strong>
            <button onclick="toggleGroup('${group}')" class="icon-btn" data-tooltip="${isCollapsed ? 'Expand Group' : 'Collapse Group'}" title="${isCollapsed ? 'Expand Group' : 'Collapse Group'}" aria-label="${isCollapsed ? 'Expand Group' : 'Collapse Group'}">
                ${isCollapsed ? '&#x25B6;' : '&#x25BC;'}
            </button>
            <button onclick="moveGroup('${group}', -1)" class="icon-btn" data-tooltip="Move Group Up" title="Move Group Up" aria-label="Move Group Up" ${groupIdx === 0 ? 'disabled' : ''}>&#8593;</button>
            <button onclick="moveGroup('${group}', 1)" class="icon-btn" data-tooltip="Move Group Down" title="Move Group Down" aria-label="Move Group Down" ${groupIdx === groupArr.length - 1 ? 'disabled' : ''}>&#8595;</button>
            ${!isCollapsed ? `
                <button onclick="deleteGroup('${group}')" class="icon-btn" data-tooltip="Delete Group" title="Delete Group" aria-label="Delete Group">&#128465;</button>
                <button onclick="reloadGroupFeeds('${group}')" class="icon-btn" data-tooltip="Reload Feeds" title="Reload Feeds" aria-label="Reload Feeds">&#x21bb;</button>
                <form onsubmit="addChannel(event, '${group}')">
                    <input type="text" placeholder="Channel ID" required>
                    <button type="submit" class="plus-btn" data-tooltip="Add Channel" title="Add Channel" aria-label="Add Channel">+</button>
                </form>
                <div class="channels">${channels.map((ch, i) => {
                    const shortsKey = `${group}:${ch.id}`;
                    const showShorts = shortsFilter[shortsKey] !== false;
                    return `
                        <div>
                            ${ch.title ? `${ch.title} (${ch.id})` : ch.id}
                            <button onclick="removeChannel('${group}', ${i})" class="icon-btn" data-tooltip="Remove Channel" title="Remove Channel" aria-label="Remove Channel">&#128465;</button>
                            <button onclick="toggleShorts('${group}','${ch.id}')" class="icon-btn" data-tooltip="${showShorts ? 'Hide Shorts' : 'Show Shorts'}" title="${showShorts ? 'Hide Shorts' : 'Show Shorts'}" aria-label="${showShorts ? 'Hide Shorts' : 'Show Shorts'}">&#x1f456;</button>
                            <button onclick="moveChannel('${group}', ${i}, -1)" class="icon-btn" data-tooltip="Move Up" title="Move Up" aria-label="Move Up" ${i === 0 ? 'disabled' : ''}>&#8593;</button>
                            <button onclick="moveChannel('${group}', ${i}, 1)" class="icon-btn" data-tooltip="Move Down" title="Move Down" aria-label="Move Down" ${i === channels.length - 1 ? 'disabled' : ''}>&#8595;</button>
                            ${ch.entries && ch.entries.length ? `
                                <div style="display: flex; gap: 1em; margin-top: 0.5em;">
                                    ${ch.entries
                                        .filter(entry => showShorts || !entry.href.includes('/shorts/'))
                                        .map(entry => `
                                            <div style="display: flex; flex-direction: column; align-items: center; width: 160px;">
                                                ${entry.thumbnail ? `<a href="${entry.href}" target="_blank" rel="noopener">
                                                    <img src="${entry.thumbnail}" alt="thumbnail" style="width:150px;height:auto;display:block;">
                                                </a>` : ''}
                                                <a href="${entry.href}" target="_blank" rel="noopener" style="margin-top: 0.3em; text-align: center; font-size: 0.95em;">
                                                    ${entry.title}
                                                </a>
                                            </div>
                                        `).join('')}
                                </div>
                            ` : ''}
                        </div>
                    `;
                }).join('')}</div>
            ` : ''}
        `;
        groupsDiv.appendChild(groupDiv);
    });
}

// --- Reload all feeds in a group ---
window.reloadGroupFeeds = async function(group) {
    const groups = getGroups();
    if (!groups[group]) return;
    const updatedChannels = await Promise.all(groups[group].map(async ch => {
        try {
            const res = await fetch(`api.php?channel_id=${encodeURIComponent(ch.id)}`);
            const data = await res.json();
            if (data && data.title) {
                return { id: ch.id, title: data.title, entries: data.entries || [] };
            }
        } catch (e) {}
        return ch;
    }));
    groups[group] = updatedChannels;
    saveGroups(groups);
    renderGroups();
};

// --- Toggle group visibility ---
window.toggleGroup = function(group) {
    const collapsedGroups = getCollapsedGroups();
    collapsedGroups[group] = !collapsedGroups[group];
    saveCollapsedGroups(collapsedGroups);
    renderGroups();
};

// --- Group actions ---
document.getElementById('group-form').onsubmit = function(e) {
    e.preventDefault();
    const name = document.getElementById('group-name').value.trim();
    if (!name) return;
    const groups = getGroups();
    if (!groups[name]) groups[name] = [];
    saveGroups(groups);
    document.getElementById('group-name').value = '';
    renderGroups();
};

window.deleteGroup = function(group) {
    if (!confirm(`Are you sure you want to delete the group "${group}" and all its channels?`)) return;
    const groups = getGroups();
    delete groups[group];
    saveGroups(groups);
    renderGroups();
};

window.addChannel = async function(e, group) {
    e.preventDefault();
    const input = e.target.querySelector('input');
    const channelId = input.value.trim();
    if (!channelId) return;
    try {
        const res = await fetch(`api.php?channel_id=${encodeURIComponent(channelId)}`);
        const data = await res.json();
        if (data && data.title) {
            const groups = getGroups();
            if (!groups[group].some(ch => ch.id === channelId)) {
                groups[group].push({ id: channelId, title: data.title, entries: data.entries || [] });
                saveGroups(groups);
            }
            input.value = '';
            renderGroups();
        } else {
            alert('Channel not found or invalid response');
        }
    } catch (err) {
        alert('Failed to fetch channel info');
    }
};

window.removeChannel = function(group, idx) {
    const groups = getGroups();
    const ch = groups[group][idx];
    if (!confirm(`Remove channel "${ch.title || ch.id}" from group "${group}"?`)) return;
    groups[group].splice(idx, 1);
    saveGroups(groups);
    renderGroups();
};

// Toggle shorts display per channel
window.toggleShorts = function(group, channelId) {
    const shortsFilter = getShortsFilter();
    const key = `${group}:${channelId}`;
    shortsFilter[key] = shortsFilter[key] === false ? true : false;
    saveShortsFilter(shortsFilter);
    renderGroups();
};

// Move channels up or down
window.moveChannel = function(group, idx, dir) {
    const groups = getGroups();
    const arr = groups[group];
    const newIdx = idx + dir;
    if (newIdx < 0 || newIdx >= arr.length) return;
    [arr[idx], arr[newIdx]] = [arr[newIdx], arr[idx]];
    saveGroups(groups);
    renderGroups();
};

// Move groups up or down
window.moveGroup = function(group, dir) {
    const groups = getGroups();
    const keys = Object.keys(groups);
    const idx = keys.indexOf(group);
    const newIdx = idx + dir;
    if (newIdx < 0 || newIdx >= keys.length) return;
    const newKeys = [...keys];
    [newKeys[idx], newKeys[newIdx]] = [newKeys[newIdx], newKeys[idx]];
    const newGroups = {};
    newKeys.forEach(k => { newGroups[k] = groups[k]; });
    saveGroups(newGroups);
    renderGroups();
};

// --- Theme toggle ---
function setTheme(dark) {
    if (dark) {
        document.body.classList.add('dark-mode');
        document.getElementById('theme-toggle').innerHTML = '&#9790;';
    } else {
        document.body.classList.remove('dark-mode');
        document.getElementById('theme-toggle').innerHTML = '&#9788;';
    }
    localStorage.setItem('darkMode', dark ? '1' : '0');
}
document.getElementById('theme-toggle').onclick = function() {
    const dark = !document.body.classList.contains('dark-mode');
    setTheme(dark);
};
setTheme(localStorage.getItem('darkMode') === '1');

// --- Export groups config ---
document.getElementById('export-btn').onclick = function() {
    const groups = getGroups();
    const exportData = {};
    Object.entries(groups).forEach(([group, channels]) => {
        exportData[group] = channels.map(ch => ch.id);
    });
    const blob = new Blob([JSON.stringify(exportData, null, 2)], {type: 'application/json'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'whatsnewtube-groups.json';
    document.body.appendChild(a);
    a.click();
    setTimeout(() => {
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }, 100);
};

// --- Import groups config ---
document.getElementById('import-btn').onclick = function() {
    document.getElementById('import-file').click();
};
document.getElementById('import-file').onchange = function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        try {
            const data = JSON.parse(ev.target.result);
            if (typeof data !== 'object' || Array.isArray(data)) throw new Error();
            for (const [group, channels] of Object.entries(data)) {
                if (!Array.isArray(channels) || !channels.every(id => typeof id === 'string')) {
                    alert('Invalid file format.');
                    return;
                }
            }
            const groups = {};
            Object.entries(data).forEach(([group, ids]) => {
                groups[group] = ids.map(id => ({ id, title: '', entries: [] }));
            });
            saveGroups(groups);
            saveCollapsedGroups({});
            saveShortsFilter({});
            renderGroups();
            alert('Groups imported successfully!');
        } catch {
            alert('Failed to import: invalid JSON or format.');
        }
    };
    reader.readAsText(file);
    e.target.value = '';
};

// --- Initial render ---
renderGroups();