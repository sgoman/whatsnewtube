<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>What's new Tube</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <button id="theme-toggle" class="icon-btn" style="float:right; margin-top:0.5em;" data-tooltip="Toggle Light/Dark Mode" title="Toggle Light/Dark Mode" aria-label="Toggle Light/Dark Mode">&#9788;</button>
    <button id="export-btn" class="icon-btn" style="float:right; margin-top:0.5em; margin-right:0.5em;" data-tooltip="Export Groups" title="Export Groups" aria-label="Export Groups">&#128190;</button>
    <button id="import-btn" class="icon-btn" style="float:right; margin-top:0.5em; margin-right:0.5em;" data-tooltip="Import Groups" title="Import Groups" aria-label="Import Groups">&#128228;</button>
    <input type="file" id="import-file" accept="application/json" style="display:none;">
    <h1>What's new Tube?!</h1>
    <form id="group-form">
        <input type="text" id="group-name" placeholder="New group name" required>
        <button type="submit" class="plus-btn" data-tooltip="Add Group" title="Add Group" aria-label="Add Group">+</button>
    </form>
    <div id="groups"></div>
    <script src="main.js"></script>
</body>
</html>