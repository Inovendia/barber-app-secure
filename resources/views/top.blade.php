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

    <!-- 🔹 Heroセクション -->
    <section class="bg-pink-100 py-20 text-center">
        <h2 class="text-4xl font-bold text-gray-800 mb-4">LINEから簡単に予約</h2>
        <p class="text-lg text-gray-700">美容室・理容室向けの予約管理ミニアプリ</p>
    </section>

    <!-- 🔹 特徴セクション -->
    <section class="max-w-5xl mx-auto py-16 px-6">
        <h3 class="text-2xl font-semibold mb-8 text-center text-gray-800">Rezamieの主な機能</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white shadow p-6 rounded-lg">
                <h4 class="text-lg font-bold text-pink-600 mb-2">LINE完結型予約</h4>
                <p class="text-gray-600">LINEミニアプリからメニュー選択〜日時予約までスムーズに完結。</p>
            </div>
            <div class="bg-white shadow p-6 rounded-lg">
                <h4 class="text-lg font-bold text-pink-600 mb-2">直感的なカレンダー</h4>
                <p class="text-gray-600">定休日や休憩時間も考慮した予約枠表示で分かりやすい。</p>
            </div>
            <div class="bg-white shadow p-6 rounded-lg">
                <h4 class="text-lg font-bold text-pink-600 mb-2">LINE通知連携</h4>
                <p class="text-gray-600">予約完了・確認・キャンセル通知をLINEで自動配信。</p>
            </div>
        </div>
    </section>

    <!-- 🔹 サポート・ポリシー -->
    <footer class="bg-gray-100 py-10 px-6 text-center text-sm text-gray-600">
        <p class="mb-2">
            ご不明な点がございましたら <a href="mailto:rezamie.info@gmail.com" class="text-blue-600 underline">rezamie.info@gmail.com</a>までお問い合わせください
        </p>
        <p>
            <a href="{{ route('support') }}" class="text-blue-600 underline">▶ カスタマーサポート</a>
        </p>
        <p>
            <a href="{{ route('privacy') }}" class="text-blue-600 underline">▶ プライバシーポリシー</a>
        </p>
    </footer>

</body>
</html>
