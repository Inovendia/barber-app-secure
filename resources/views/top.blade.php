{{-- resources/views/top.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rezamie</title>

  {{-- Tailwind 読み込み（Vite 経由） --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root{
      --ink:#1e293b; --sub:#475569; --muted:#64748b;
      --brand:#0f6fff; --accent:#e6007e; --card:#ffffff;
      --shadow:0 10px 30px rgba(15,30,60,.08);
      --radius:14px;
    }

    body { background:#fff; color:var(--ink); font-family:"Figtree",sans-serif; }
    .rz-container{max-width:1200px;margin:0 auto;padding:0 20px}

    /* Heroセクション */
    .rz-hero {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 100px 20px;
      background: linear-gradient(135deg, #00a3ff 0%, #00c9ff 50%, #ffffff 100%);
      color: white;
      overflow: hidden;
    }
    .rz-hero img {
      position: absolute;
      right: 0; bottom: 0;
      width: 50%;
      height: 100%;
      object-fit: cover;
      opacity: 0.9;
    }
    .rz-hero h1 {
      position: relative;
      z-index: 2;
      font-size: clamp(28px, 4vw, 52px);
      font-weight: bold;
      line-height: 1.3;
      max-width: 600px;
    }

    /* SaaS紹介 */
    .rz-intro {
      background: #fff;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      max-width: 1000px;
      margin: -40px auto 60px;
      padding: 40px 20px;
      text-align: center;
    }
    .rz-intro h2 {
      font-size: 1.6rem;
      font-weight: bold;
      margin-bottom: 0.5rem;
    }
    .rz-intro p {
      color: var(--muted);
      font-size: 0.95rem;
      margin-bottom: 2rem;
    }

    /* 機能カード */
    .rz-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 24px;
      margin: 24px 0 60px;
    }
    .rz-card {
      background: var(--card);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      text-align: center;
      padding: 32px 20px;
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .rz-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    .rz-card h3 {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: .5rem;
    }
    .rz-card p {
      color: var(--sub);
      line-height: 1.6;
      font-size: .95rem;
    }

    /* 特徴ブロック */
    .rz-feature {
      background: #f9fafb;
      padding: 80px 0;
    }
    .rz-fd {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 32px;
      margin-bottom: 80px;
    }
    .rz-fd.reverse { flex-direction: row-reverse; }
    .rz-fd .text { flex:1 1 400px; }
    .rz-fd .visual { flex:1 1 400px; text-align:center; }
    .rz-fd img { border-radius: var(--radius); box-shadow: var(--shadow); width:100%; height:auto; }
    .rz-fd .num {
      display:inline-block;
      font-weight:bold;
      font-size:2rem;
      color:var(--accent);
      margin-bottom:8px;
    }

    /* フッター帯 */
    .rz-footband{
      background:#f9fafb;
      text-align:center;
      padding:20px 10px;
      font-size:0.9rem;
      color:var(--sub);
    }
    .rz-footband a{
      color:var(--accent);
      text-decoration:underline;
      margin:0 4px;
    }
  </style>
</head>

<body>

  {{-- Heroセクション --}}
  <section class="rz-hero">
    <h1>LINEで、理美容の予約をもっと簡単に</h1>
    <img src="{{ asset('images/###hero_salon.jpg') }}" alt="美容室で接客するスタッフの写真">
  </section>

  {{-- SaaS紹介 --}}
  <section class="rz-intro rz-container">
    <h2>Rezamieの主な機能（複数店舗で利用可能なSaaS型サービス）</h2>
    <p>サービス全体はRezamieが運営し、各店舗は利用者として導入できます。</p>

    <div class="rz-cards">
      <article class="rz-card">
        <img src="{{ asset('images/###icon_line.png') }}" alt="LINEアイコン" class="mx-auto mb-4 w-14 h-14">
        <h3>LINE完結の予約体験</h3>
        <p>メニュー選択から日時確定まで、LINEミニアプリでスムーズに完了。</p>
      </article>
      <article class="rz-card">
        <img src="{{ asset('images/###icon_calendar.png') }}" alt="カレンダーアイコン" class="mx-auto mb-4 w-14 h-14">
        <h3>わかりやすい予約カレンダー</h3>
        <p>所要時間・定休日・休憩時間を加味し、ムリのない枠表示で迷わない。</p>
      </article>
      <article class="rz-card">
        <img src="{{ asset('images/###icon_bell.png') }}" alt="通知アイコン" class="mx-auto mb-4 w-14 h-14">
        <h3>通知・確認も自動で</h3>
        <p>予約完了／確認／キャンセルをLINEで自動通知。店舗とお客様の手間を削減。</p>
      </article>
    </div>
  </section>

  {{-- 特徴詳細 --}}
  <section class="rz-feature">
    <div class="rz-container">

      <div class="rz-fd">
        <div class="text">
          <span class="num">01</span>
          <h3>“空き状況がひと目で分かる”予約カレンダー</h3>
          <p>メニュー所要時間や定休日・休憩時間を考慮し、ダブルブッキングを自動防止。</p>
        </div>
        <div class="visual">
          <img src="{{ asset('images/###feature_01.png') }}" alt="予約カレンダー画面">
        </div>
      </div>

      <div class="rz-fd reverse">
        <div class="text">
          <span class="num">02</span>
          <h3>LINE連携でリマインド・キャンセルもスマート</h3>
          <p>予約完了・前日リマインド・キャンセル受付をLINEで完結。無断キャンセルを防ぎます。</p>
        </div>
        <div class="visual">
          <img src="{{ asset('images/###feature_02.png') }}" alt="LINE通知画面">
        </div>
      </div>

      <div class="rz-fd">
        <div class="text">
          <span class="num">03</span>
          <h3>管理者ダッシュボードで予約状況と顧客メモを一元管理</h3>
          <p>当日・翌日の予約確認、顧客メモ、カレンダー記号（×・tel・◎）の手動設定など運用を集約。</p>
        </div>
        <div class="visual">
          <img src="{{ asset('images/###feature_03.png') }}" alt="管理者ダッシュボード画面">
        </div>
      </div>

    </div>
  </section>

  {{-- フッター帯 --}}
  <div class="rz-footband">
    本サービスの提供主体（プロバイダー）はRezamieです。 /
    <a href="mailto:rezamie.info@gmail.com">rezamie.info@gmail.com</a> /
    <a href="{{ url('/support') }}">カスタマーサポート</a> /
    <a href="{{ url('/privacy') }}">プライバシーポリシー</a>
  </div>

</body>
</html>
