<?php defined('SAMS_ROOT') || exit; ?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="refresh" content="20">
<title>Wartung · SAMS</title>
<style>
    body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #eef1f5; color: #1d2733;
        font-family: "Segoe UI", system-ui, sans-serif; }
    .box { background: #fff; border-radius: 8px; padding: 2rem 2.4rem; max-width: 460px; text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,.08); }
    h1 { font-size: 1.3rem; margin: .8rem 0 .4rem; }
    p { color: #6b7785; margin: 0; }
    .spin { width: 36px; height: 36px; border: 4px solid #dde3ea; border-top-color: #0b4f9c; border-radius: 50%;
        margin: 0 auto; animation: s 1s linear infinite; }
    @keyframes s { to { transform: rotate(360deg); } }
</style>
</head>
<body>
<div class="box">
    <div class="spin"></div>
    <h1>Update wird eingespielt</h1>
    <p>Das System ist in wenigen Augenblicken wieder erreichbar. Die Seite lädt automatisch neu.</p>
</div>
</body>
</html>
