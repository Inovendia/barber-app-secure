<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>LIFFデバッグモード</title>
  <style>
    body { font-family: sans-serif; text-align:center; margin-top: 5rem; }
    input { padding:8px; margin:5px; }
    button { padding:10px 20px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer; }
  </style>
</head>
<body>
  <h2>🔍 LIFFデバッグページ</h2>
  <p>※LINEを経由せずに <code>/liff/entry</code> の動作確認を行います。</p>

  <form method="POST" action="/liff/entry">
    @csrf
    <div>
      <label>LINEユーザーID（任意）</label><br>
      <input type="text" name="line_user_id" value="UdebugTest123" />
    </div>
    <div>
      <label>shop_id（任意）</label><br>
      <input type="text" name="shop_id" value="1" />
    </div>
    <button type="submit">POST送信（テスト）</button>
  </form>

  <p style="margin-top:2rem;">送信後、302リダイレクトが発生すれば成功です。</p>
</body>
</html>
