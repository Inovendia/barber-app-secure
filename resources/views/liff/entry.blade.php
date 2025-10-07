<!doctype html><html lang="ja"><head>
<meta charset="utf-8"><title>Rezamie</title>
<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
</head><body>
<script>
(async () => {
  console.log("💡 LIFF entry script started");

  await liff.init({ liffId: "{{ config('liff.liff_id') }}" });
  console.log("✅ LIFF initialized");

  if (!liff.isLoggedIn()) {
    console.log("⚠️ Not logged in → redirecting to LINE login");
    liff.login();
    return;
  }

  const profile = await liff.getProfile();
  console.log("👤 LINE user profile", profile);

  const lineUserId = profile.userId;
  console.log("📡 Posting to /liff/entry ...", { lineUserId });

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/liff/entry';
  const input = document.createElement('input');
  input.type = 'hidden'; input.name = 'line_user_id'; input.value = lineUserId;
  form.appendChild(input);
  document.body.appendChild(form);
  form.submit();
})();
</script>
</body></html>
