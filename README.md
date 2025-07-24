# attendance_management

🕒 勤怠管理アプリ

従業員の出勤・退勤・休憩時間を記録し、管理者が申請を承認できる勤怠管理アプリです。

---

✅ 環境構築手順

### 📦 クローン


git clone https://github.com/hide32ma/attendance_management.git

cd attendance_management

### 🐳 Docker起動

docker-compose up -d --build

### ⚙️ Laravelセットアップ（Dockerコンテナ内で）

docker-compose exec php bash

#以下、コンテナ内で実行

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan db:seed

### 🧪 テストアカウント（一般ユーザー）

name:Cyril Krajcik MD

email:iokon@example.com


name:Mr. Savion Grant Jr.

email:sallie.spencer@example.org


name:Katrina Smitham

email:abby.bahringer@example.org


password:11111111（パスワードは全ての一般ユーザーが同じ）

（上記含め、10個のテストアカウントあり）


### 🧪 テストアカウント（管理者）

email:zaria90@example.com

email:levi.considine@example.org

email:ernie46@example.net

password:11111111（パスワードは全ての管理者が同じ）

（上記含め、10個のテストアカウントあり）

### 勤怠情報のダミーデータは、2025年1月〜2025年12月までのデータが作られる設定にしています


### 🧪 テスト実行方法（PHPUnit）
php artisan test

### 特定のテストを実行する場合（例：ClockOutTest）
php artisan test --filter ClockOutTest

テスト結果はPASS/FAILで出力され、実装内容が正しく動作するかを確認できます。

### 🛠 使用技術
項目	バージョン
Laravel	8.75以上

PHP	8.0(7.3以上対応)

MySQL	8.0.26

Docker	20.x以上

docker-compose	1.29以上

### 🔗 アクセスURL（ローカル開発環境）
アプリ本体 : http://localhost

phpMyAdmin : http://localhost:8080

