@extends('layouts.app')
@section('title', 'Rezamie')

@push('styles')
<style>
  :root{
    --bg-grad-start:#f0f7ff; --bg-grad-end:#ffffff;
    --ink:#1e293b; --sub:#475569; --muted:#64748b;
    --brand:#0f6fff; --accent:#e6007e; --card:#ffffff;
    --shadow:0 10px 30px rgba(15,30,60,.08);
    --radius:14px;
  }
  .rz-container{max-width:1200px;margin:0 auto;padding:0 20px}

  /* Hero（上部の説明） */
  .rz-hero{padding:56px 0 24px;text-align:center;background:linear-gradient(180deg,var(--bg-grad-start),var(--bg-grad-end));}
  .rz-hero h1{font-size:clamp(28px,4vw,48px);line-height:1.2;margin:0 0 12px;color:var(--ink)}
  .rz-hero p{max-width:860px;margin:0 auto;color:var(--muted)}

  /* ① h2上の大きな画像 */
  .rz-wide-wrap{position:relative;margin:48px auto 24px}
  .rz-wide-bg{position:absolute;inset:-24px -16px -40px -16px;z-index:0;
    background: radial-gradient(120px 80px at 12% 18%, #ff5db1 14%, transparent 50%),
                radial-gradient(160px 120px at 88% 35%, #00a3ff 12%, transparent 55%),
                radial-gradient(140px 90px at 28% 92%, #ffd34f 14%, transparent 55%);
    filter: blur(35px); opacity:.25; pointer-events:none;}
  .rz-wide{position:relative;z-index:1;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);background:#000}
  .rz-wide img{display:block;width:100%;height:auto;object-fit:cover}
</style>
@endpush

@section('content')

  {{-- Hero（上部のコピー） --}}
  <section class="rz-hero">
    <div class="rz-container">
      <h1>LINEで、理美容の予約をもっと簡単に</h1>
      <p>
        複数店舗で使えるSaaS型予約プラットフォーム「Rezamie」。ユーザー情報はRezamieが責任をもって管理します。<br>
        Rezamieは理美容店舗向けの共通予約システムです。各店舗は本サービスの利用者であり、サービス提供主体はRezamieです。
      </p>
    </div>
  </section>

  {{-- ① h2の上に大きな画像 --}}
  <div class="rz-container rz-wide-wrap" aria-label="製品イメージ">
    <div class="rz-wide-bg"></div>
    <div class="rz-wide">
      {{-- public/images/hero-large.png に配置してください --}}
      <img src="{{ asset('images/hero-large.png') }}" alt="Rezamie 美容室・サロンの雰囲気画像">
    </div>
  </div>

  {{-- 機能見出し --}}
  <div class="rz-container rz-sec-head">
    <h2>Rezamieの主な機能（複数店舗で利用可能なSaaS型サービス）</h2>
    <p>各店舗ごとに個別導入できるのではなく、共通のプラットフォームとしてご利用いただけます。</p>
  </div>

  {{-- 機能カード --}}
  <section class="rz-container rz-cards">
    <article class="rz-card">
      <h3>LINE完結の予約体験</h3>
      <p>メニュー選択から日時確定まで、LINEミニアプリでスムーズに完了。</p>
    </article>
    <article class="rz-card">
      <h3>わかりやすい予約カレンダー</h3>
      <p>所要時間・定休日・休憩時間を加味し、ムリのない枠表示で迷わない。</p>
    </article>
    <article class="rz-card">
      <h3>通知・確認も自動で</h3>
      <p>予約完了／確認／キャンセルをLINEで自動通知。店舗とお客様の手間を削減。</p>
    </article>
  </section>

  {{-- 特徴詳細（CastMe風） --}}
  <section class="rz-feature">
    <div class="rz-container">

      <div class="rz-fd">
        <div class="text">
          <span class="num">01</span>
          <h3>“空き状況がひと目で分かる” 予約カレンダー</h3>
          <p>メニューの所要時間を自動で反映。定休日・休憩・当日残り時間の制限も考慮して、ダブルブッキングを防ぎます。</p>
        </div>
        <div class="visual">
          <img src="{{ asset('images/feature-01.png') }}" alt="予約カレンダー画面のスクリーンショット">
        </div>
      </div>

      <div class="rz-fd reverse">
        <div class="text">
          <span class="num">02</span>
          <h3>LINE連携でリマインド・キャンセルもスマート</h3>
          <p>予約完了・前日リマインド・キャンセル受付をLINEで完結。無断キャンセル抑止と問い合わせ削減に貢献します。</p>
        </div>
        <div class="visual">
          <img src="{{ asset('images/feature-02.png') }}" alt="LINE通知・確認メッセージの例">
        </div>
      </div>

      <div class="rz-fd">
        <div class="text">
          <span class="num">03</span>
          <h3>管理者ダッシュボードで予約状況と顧客メモを一元管理</h3>
          <p>本日の予約・明日以降、顧客メモ・画像管理、カレンダー記号（×・tel・◎）の手動設定まで、運用に必要な機能を統合。</p>
        </div>
        <div class="visual">
          <img src="{{ asset('images/feature-03.png') }}" alt="管理画面ダッシュボードのスクリーンショット">
        </div>
      </div>

    </div>
  </section>

  {{-- フッター帯 --}}
  <div class="rz-footband">
    本サービスの提供主体（プロバイダー）はRezamieです。/
    @if (View::exists('partials.footer-links'))
      @include('partials.footer-links')
    @else
      <a href="mailto:rezamie.info@gmail.com">rezamie.info@gmail.com</a> /
      <a href="{{ url('/support') }}">カスタマーサポート</a> /
      <a href="{{ url('/privacy') }}">プライバシーポリシー</a>
    @endif
  </div>
@endsection
