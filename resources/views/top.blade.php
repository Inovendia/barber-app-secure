<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Rezamie - LINE予約ミニアプリ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- 🔹 ナビゲーションバー -->
    <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/rezamie_logo.png') }}" alt="Rezamieロゴ" class="w-8 h-8">
            <span class="text-xl font-bold text-pink-600">Rezamie</span>
        </div>
        <a href="{{ url('/admin/login') }}" class="text-blue-600 font-medium hover:underline">ログインはこちら</a>
    </header>

    <main class="max-w-5xl mx-auto px-6">

        {{-- ヒーロー --}}
        <section class="text-center py-12">
            <h1 class="text-3xl sm:text-5xl font-bold text-gray-800">
                LINEで、理美容の予約をもっと簡単に
            </h1>
            <p class="mt-4 text-lg text-gray-600">
                複数店舗で使えるSaaS型予約プラットフォーム「Rezamie」。ユーザー情報はRezamieが責任をもって管理します。
            </p>
            <p class="mt-2 text-gray-500 text-sm">
                Rezamieは理美容店舗向けの共通予約システムです。各店舗は本サービスの利用者であり、サービス提供主体はRezamieです。
            </p>
        </section>

        {{-- 機能見出し --}}
        <section class="mt-16 text-center">
            <h2 class="text-2xl font-extrabold">
                Rezamieの主な機能（複数店舗で利用可能なSaaS型サービス）
            </h2>
            <p class="mt-2 text-gray-600">
                各店舗ごとに個別導入できるのではなく、共通のプラットフォームとしてご利用いただけます。
            </p>

            {{-- 機能カード --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h3 class="text-pink-600 font-bold mb-2">LINE完結の予約体験</h3>
                    <p class="text-gray-600">メニュー選択から日時確定までLINEミニアプリでスムーズに完了。</p>
                </div>
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h3 class="text-pink-600 font-bold mb-2">わかりやすい予約カレンダー</h3>
                    <p class="text-gray-600">所要時間・定休日・休憩時間を加味した枠表示で迷わない。</p>
                </div>
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h3 class="text-pink-600 font-bold mb-2">通知・確認も自動で</h3>
                    <p class="text-gray-600">予約完了／確認／キャンセルをLINEで自動通知。店舗とお客様の手間を削減。</p>
                </div>
            </div>
        </section>

    </main>

    {{-- フッター案内 --}}
    <footer class="bg-gray-100 mt-16 py-8 text-center text-sm text-gray-600">
        <p>
            本サービスの提供主体（プロバイダー）は<strong>Rezamie</strong>です。導入店舗は本サービスの利用者です。<br>
            最終更新日：2025年8月
        </p>
        <p class="mt-2">
            <a href="mailto:rezamie.info@gmail.com" class="text-blue-600 underline">rezamie.info@gmail.com</a> ／
            <a href="{{ route('support') }}" class="text-blue-600 underline">カスタマーサポート</a> ／
            <a href="{{ route('privacy') }}" class="text-blue-600 underline">プライバシーポリシー</a>
        </p>
    </footer>

</body>
</html>
