<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>マイ予約</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <p id="msg">読み込み中...</p>

  <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
  <script>
  (async () => {
    try {
      await liff.init({ liffId: "{{ config('services.liff.id') }}" });
      if (!liff.isLoggedIn()) { liff.login(); return; }

      const profile = await liff.getProfile();

      const res = await fetch("{{ route('reserve.resolve') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ line_user_id: profile.userId })
      });

      const data = await res.json();

      if (data.token) {
        location.href = "{{ route('reserve.verify') }}?token=" + encodeURIComponent(data.token);
      } else {
        document.getElementById('msg').textContent = "直近の予約は見つかりません。";
      }
    } catch (e) {
      document.getElementById('msg').textContent = "エラーが発生しました。";
      console.error(e);
    }
  })();
  </script>
</body>
</html>
