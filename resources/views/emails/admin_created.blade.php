<p>{{ $admin->name }}様</p>

<p>管理者アカウントが作成されました。以下の情報でログインしてください。</p>

<p><strong>ログインURL：</strong><br>
<a href="{{ url('/admin/login') }}">{{ url('/admin/login') }}</a></p>

<p><strong>ログインID（メールアドレス）：</strong><br>
{{ $admin->email }}</p>

<p><strong>初期パスワード：</strong><br>
{{ $password }}</p>

<p>※ログイン後は速やかにパスワードの変更をお願いいたします。</p>
