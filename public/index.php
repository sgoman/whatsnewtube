<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>What's new Tube</title>
    <style>
        body { font-family: sans-serif; margin: 2em; }
        .group { margin-bottom: 1em; }
        .channels { margin-left: 1em; }
        .plus-btn {
            background: none;
            border: none;
            color: #2ecc40;
            font-size: 1.5em;
            cursor: pointer;
            vertical-align: middle;
            padding: 0 0.3em;
        }
        .plus-btn:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            background: #222;
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            left: 2em;
            top: 0.2em;
            white-space: nowrap;
            z-index: 10;
            font-size: 0.95em;
        }
        .plus-btn:focus { outline: 2px solid #2ecc40; }
        form { display: inline; }
        .icon-btn {
            background: none;
            border: none;
            color: #e74c3c;
            font-size: 1.3em;
            cursor: pointer;
            vertical-align: middle;
            padding: 0 0.3em;
            position: relative;
        }
        .icon-btn:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            background: #222;
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            left: 2em;
            top: 0.2em;
            white-space: nowrap;
            z-index: 10;
            font-size: 0.95em;
        }
        .icon-btn:focus { outline: 2px solid #e74c3c; }
        body.dark-mode {
            background: #181818;
            color: #eee;
        }
        body.dark-mode input,
        body.dark-mode button,
        body.dark-mode select,
        body.dark-mode textarea {
            background: #222;
            color: #eee;
            border-color: #444;
        }
        body.dark-mode .group {
            background: #222;
            border-radius: 6px;
            padding: 0.5em 1em;
        }
        body.dark-mode .plus-btn,
        body.dark-mode .icon-btn {
            color: #2ecc40;
        }
        body.dark-mode .icon-btn[data-tooltip]:hover::after,
        body.dark-mode .plus-btn[data-tooltip]:hover::after {
            background: #444;
            color: #fff;
        }
        body.dark-mode a,
        body.dark-mode a:visited {
            color: #80c8ff; /* Light blue for high contrast */
            text-decoration: underline;
        }
        body.dark-mode a:hover {
            color: #fff700; /* Bright yellow on hover */
        }
    </style>
</head>
<body>
    <button id="theme-toggle" class="icon-btn" style="float:right; margin-top:0.5em;" data-tooltip="Toggle Light/Dark Mode" title="Toggle Light/Dark Mode" aria-label="Toggle Light/Dark Mode">&#9788;</button>
    <h1>What's new Tube?!</h1>
    <form id="group-form">
        <input type="text" id="group-name" placeholder="New group name" required>
        <button type="submit" class="plus-btn" data-tooltip="Add Group" title="Add Group" aria-label="Add Group">+</button>
    </form>
    <div id="groups"></div>
    <script>
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

        // Add this helper to get shorts filter state per channel:
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
            Object.entries(groups).forEach(([group, channels]) => {
                const groupDiv = document.createElement('div');
                groupDiv.className = 'group';
                const isCollapsed = collapsedGroups[group];
                groupDiv.innerHTML = `
                    <strong>${group}</strong>
                    <button onclick="toggleGroup('${group}')" class="icon-btn" data-tooltip="${isCollapsed ? 'Expand Group' : 'Collapse Group'}" title="${isCollapsed ? 'Expand Group' : 'Collapse Group'}" aria-label="${isCollapsed ? 'Expand Group' : 'Collapse Group'}">
                        ${isCollapsed ? '&#x25B6;' : '&#x25BC;'}
                    </button>
                    ${!isCollapsed ? `
                        <button onclick="deleteGroup('${group}')" class="icon-btn" data-tooltip="Delete Group" title="Delete Group" aria-label="Delete Group">&#128465;</button>
                        <button onclick="reloadGroupFeeds('${group}')" class="icon-btn" data-tooltip="Reload Feeds" title="Reload Feeds" aria-label="Reload Feeds">&#x21bb;</button>
                        <form onsubmit="addChannel(event, '${group}')">
                            <input type="text" placeholder="Channel ID" required>
                            <button type="submit" class="plus-btn" data-tooltip="Add Channel" title="Add Channel" aria-label="Add Channel">+</button>
                        </form>
                        <div class="channels">${channels.map((ch, i) => {
                            const shortsKey = `${group}:${ch.id}`;
                            const showShorts = shortsFilter[shortsKey] !== false; // default: show shorts
                            return `
                                <div>
                                    ${ch.title ? `${ch.title} (${ch.id})` : ch.id}
                                    <button onclick="removeChannel('${group}', ${i})" class="icon-btn" data-tooltip="Remove Channel" title="Remove Channel" aria-label="Remove Channel">&#128465;</button>
                                    <button onclick="toggleShorts('${group}','${ch.id}')" class="icon-btn" data-tooltip="${showShorts ? 'Hide Shorts' : 'Show Shorts'}" title="${showShorts ? 'Hide Shorts' : 'Show Shorts'}" aria-label="${showShorts ? 'Hide Shorts' : 'Show Shorts'}">&#x1f456;</button>
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

        // Add this function to reload all feeds in a group
        window.reloadGroupFeeds = async function(group) {
            const groups = getGroups();
            if (!groups[group]) return;
            // Fetch updated info for all channels in the group
            const updatedChannels = await Promise.all(groups[group].map(async ch => {
                try {
                    const res = await fetch(`api.php?channel_id=${encodeURIComponent(ch.id)}`);
                    const data = await res.json();
                    if (data && data.title) {
                        return { id: ch.id, title: data.title, entries: data.entries || [] };
                    }
                } catch (e) {}
                // If fetch fails, keep the old data
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

            // Fetch channel info from backend
            try {
                const res = await fetch(`api.php?channel_id=${encodeURIComponent(channelId)}`);
                const data = await res.json();
                if (data && data.title) {
                    const groups = getGroups();
                    // Prevent duplicates by channel ID
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

        // Add this function to toggle shorts display per channel:
        window.toggleShorts = function(group, channelId) {
            const shortsFilter = getShortsFilter();
            const key = `${group}:${channelId}`;
            shortsFilter[key] = shortsFilter[key] === false ? true : false;
            saveShortsFilter(shortsFilter);
            renderGroups();
        };

        // --- Theme toggle ---
        function setTheme(dark) {
            if (dark) {
                document.body.classList.add('dark-mode');
                document.getElementById('theme-toggle').innerHTML = '&#9790;'; // Moon
            } else {
                document.body.classList.remove('dark-mode');
                document.getElementById('theme-toggle').innerHTML = '&#9788;'; // Sun
            }
            localStorage.setItem('darkMode', dark ? '1' : '0');
        }
        document.getElementById('theme-toggle').onclick = function() {
            const dark = !document.body.classList.contains('dark-mode');
            setTheme(dark);
        };
        // On load, set theme from localStorage
        setTheme(localStorage.getItem('darkMode') === '1');

        // --- Initial render ---
        renderGroups();
    </script>
</body>
</html>