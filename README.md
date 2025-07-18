# attendance_management

# 🕒 勤怠管理アプリ

従業員の出勤・退勤・休憩時間を記録し、管理者が申請を承認できる勤怠管理アプリです。

---

## ✅ 環境構築手順

### 📦 クローン


git clone https://github.com/hide32ma/attendance_management.git

cd attendance_management

🐳 Docker起動

docker-compose up -d --build

⚙️ Laravelセットアップ（Dockerコンテナ内で）

docker-compose exec php bash

# 以下、コンテナ内で実行
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed

🧪 テスト実行方法（PHPUnit）
php artisan test
# 特定のテストを実行する場合（例：ClockOutTest）
php artisan test --filter ClockOutTest
テスト結果はPASS/FAILで出力され、実装内容が正しく動作するかを確認できます。

🛠 使用技術
項目	バージョン
Laravel	8.x
PHP	8.0
MySQL	8.0
Docker	20.x 以上
docker-compose	1.29 以上

🔗 アクセスURL（ローカル開発環境）
アプリ本体 : http://localhost

phpMyAdmin : http://localhost:8080

