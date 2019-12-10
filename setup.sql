CREATE TABLE `users` (
  `id` int AUTO_INCREMENT,
  `username` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) COLLATE utf8_general_ci NOT NULL,
  `role` int NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `users` (`username`, `password`, `role`) VALUES
('user', '$2y$10$Fn26scxVobuHaHz2sNtYDeP3x703QAsVjCLj4pdQdMpjGq78ma/MC', 0);
-- user / user@123

/*
  * id
  * label_name:varchar(255),フォームのラベル
  * schema_name:varchar(255),form_valuesのカラム名に利用
  * input_type:varchar(255), フォームのinput_typeに利用
  * type:int, form_valuesの型に利用 ex)1=string, 2=num, 3=datetime, 4=boolean
  * required:boolean, 必須項目フラグ
  * format_with:varchar(255),バリデーション正規表現
  * updated_at:datetime
  * created_at:datetime
*/

CREATE TABLE `form_items` (
  `id` int AUTO_INCREMENT,
  `label_name` varchar(255) NOT NULL,
  `schema_name` varchar(255) COLLATE utf8_general_ci NOT NULL,
  `input_type` varchar(255) COLLATE utf8_general_ci NOT NULL,
  -- `type` varchar(255) COLLATE utf8_general_ci NOT NULL,
  `type` int NOT NULL DEFAULT 1,
  `required` boolean NOT NULL DEFAULT false,
  `format_with` varchar(255),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- INSERT INTO `form_items` (`label_name`, `schema_name`, `input_type`, `type`, `required`, `only_int`, `choice_name`, `choice_value`, `format_with`) VALUES
-- ('名前', 'name', 'text', 1, false, false, '', '', ''),
-- ('メールアドレス', 'email', 'email', 1, false, false, '', '', '/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/'),
-- ('電話番号', 'tel', 'text', 2, false, true, '', '', '/^[0-9]+$/'),
-- ('性別', 'sex', 'radio', 1, false, false, '男性,女性', 'male,female', ''),
-- ('興味', 'interest', 'checkbox', 1, false, false, '技術、環境、経済', 'technology,environment,buissiness', ''),
-- ('備考', 'remarks', 'textarea', 1, false, false, '', '', '');

CREATE TABLE `form_submits` (
  `id` int AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE `form_values` (
  `id` int AUTO_INCREMENT,
  `submit_id` int,
  `colmun_name` varchar(255),
  `text` varchar(255),
  `int` int,
  `datetime` timestamp,
  `bool` boolean,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;