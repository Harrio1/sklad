<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute должен быть принят.',
    'accepted_if' => ':attribute должен быть принят, когда :other равно :value.',
    'active_url' => ':attribute должен быть действительным URL.',
    'after' => ':attribute должен быть датой после :date.',
    'after_or_equal' => ':attribute должен быть датой после или равной :date.',
    'alpha' => ':attribute должен содержать только буквы.',
    'alpha_dash' => ':attribute должен содержать только буквы, цифры, дефисы и подчеркивания.',
    'alpha_num' => ':attribute должен содержать только буквы и цифры.',
    'array' => ':attribute должен быть массивом.',
    'ascii' => ':attribute должен содержать только однобайтовые буквенно-цифровые символы.',
    'before' => ':attribute должен быть датой до :date.',
    'before_or_equal' => ':attribute должен быть датой до или равной :date.',
    'between' => [
        'array' => ':attribute должен содержать от :min до :max элементов.',
        'file' => ':attribute должен быть между :min и :max килобайтами.',
        'numeric' => ':attribute должен быть между :min и :max.',
        'string' => ':attribute должен быть от :min до :max символов.',
    ],
    'boolean' => ':attribute должен быть true или false.',
    'can' => ':attribute содержит недопустимое значение.',
    'confirmed' => 'Подтверждение :attribute не совпадает.',
    'current_password' => 'Пароль неверный.',
    'date' => ':attribute должен быть действительной датой.',
    'date_equals' => ':attribute должен быть датой, равной :date.',
    'date_format' => ':attribute должен соответствовать формату :format.',
    'decimal' => ':attribute должен иметь :decimal десятичных знаков.',
    'declined' => ':attribute должен быть отклонен.',
    'declined_if' => ':attribute должен быть отклонен, когда :other равно :value.',
    'different' => ':attribute и :other должны различаться.',
    'digits' => ':attribute должен быть :digits цифрами.',
    'digits_between' => ':attribute должен быть между :min и :max цифрами.',
    'dimensions' => ':attribute имеет недопустимые размеры изображения.',
    'distinct' => ':attribute имеет повторяющееся значение.',
    'doesnt_end_with' => ':attribute не должен оканчиваться одним из следующих: :values.',
    'doesnt_start_with' => ':attribute не должен начинаться с одного из следующих: :values.',
    'email' => ':attribute должен быть действительным адресом электронной почты.',
    'ends_with' => ':attribute должен оканчиваться одним из следующих: :values.',
    'enum' => 'Выбранный :attribute недопустим.',
    'exists' => 'Выбранный :attribute недопустим.',
    'extensions' => ':attribute должен иметь одно из следующих расширений: :values.',
    'file' => ':attribute должен быть файлом.',
    'filled' => ':attribute должен иметь значение.',
    'gt' => [
        'array' => ':attribute должен содержать больше :value элементов.',
        'file' => ':attribute должен быть больше :value килобайт.',
        'numeric' => ':attribute должен быть больше :value.',
        'string' => ':attribute должен быть больше :value символов.',
    ],
    'gte' => [
        'array' => ':attribute должен содержать :value элементов или больше.',
        'file' => ':attribute должен быть больше или равно :value килобайтам.',
        'numeric' => ':attribute должен быть больше или равно :value.',
        'string' => ':attribute должен быть больше или равно :value символам.',
    ],
    'hex_color' => ':attribute должен быть допустимым шестнадцатеричным цветом.',
    'image' => ':attribute должен быть изображением.',
    'in' => 'Выбранный :attribute недопустим.',
    'in_array' => ':attribute должен существовать в :other.',
    'integer' => ':attribute должен быть целым числом.',
    'ip' => ':attribute должен быть действительным IP-адресом.',
    'ipv4' => ':attribute должен быть действительным IPv4-адресом.',
    'ipv6' => ':attribute должен быть действительным IPv6-адресом.',
    'json' => ':attribute должен быть допустимой JSON-строкой.',
    'lowercase' => ':attribute должен быть в нижнем регистре.',
    'lt' => [
        'array' => ':attribute должен содержать меньше :value элементов.',
        'file' => ':attribute должен быть меньше :value килобайт.',
        'numeric' => ':attribute должен быть меньше :value.',
        'string' => ':attribute должен быть меньше :value символов.',
    ],
    'lte' => [
        'array' => ':attribute не должен содержать больше :value элементов.',
        'file' => ':attribute должен быть меньше или равно :value килобайтам.',
        'numeric' => ':attribute должен быть меньше или равно :value.',
        'string' => ':attribute должен быть меньше или равно :value символам.',
    ],
    'mac_address' => ':attribute должен быть действительным MAC-адресом.',
    'max' => [
        'array' => ':attribute не должен содержать больше :max элементов.',
        'file' => ':attribute не должен быть больше :max килобайт.',
        'numeric' => ':attribute не должен быть больше :max.',
        'string' => ':attribute не должен быть больше :max символов.',
    ],
    'max_digits' => ':attribute не должен содержать больше :max цифр.',
    'mimes' => ':attribute должен быть файлом типа: :values.',
    'mimetypes' => ':attribute должен быть файлом типа: :values.',
    'min' => [
        'array' => ':attribute должен содержать как минимум :min элементов.',
        'file' => ':attribute должен быть как минимум :min килобайт.',
        'numeric' => ':attribute должен быть как минимум :min.',
        'string' => ':attribute должен содержать как минимум :min символов.',
    ],
    'min_digits' => ':attribute должен содержать как минимум :min цифр.',
    'missing' => ':attribute должен отсутствовать.',
    'missing_if' => ':attribute должен отсутствовать, когда :other равно :value.',
    'missing_unless' => ':attribute должен отсутствовать, если :other не :value.',
    'missing_with' => ':attribute должен отсутствовать, когда :values присутствует.',
    'missing_with_all' => ':attribute должен отсутствовать, когда :values присутствуют.',
    'multiple_of' => ':attribute должен быть кратно :value.',
    'not_in' => 'Выбранный :attribute недопустим.',
    'not_regex' => 'Формат :attribute недопустим.',
    'numeric' => ':attribute должен быть числом.',
    'password' => [
        'letters' => ':attribute должен содержать как минимум одну букву.',
        'mixed' => ':attribute должен содержать как минимум одну прописную и одну строчную букву.',
        'numbers' => ':attribute должен содержать как минимум одну цифру.',
        'symbols' => ':attribute должен содержать как минимум один символ.',
        'uncompromised' => 'Указанный :attribute появился в утечке данных. Пожалуйста, выберите другой :attribute.',
    ],
    'present' => ':attribute должен присутствовать.',
    'present_if' => ':attribute должен присутствовать, когда :other равно :value.',
    'present_unless' => ':attribute должен присутствовать, если :other не :value.',
    'present_with' => ':attribute должен присутствовать, когда :values присутствует.',
    'present_with_all' => ':attribute должен присутствовать, когда :values присутствуют.',
    'prohibited' => ':attribute запрещен.',
    'prohibited_if' => ':attribute запрещен, когда :other равно :value.',
    'prohibited_unless' => ':attribute запрещен, если :other не в :values.',
    'prohibits' => ':attribute запрещает присутствие :other.',
    'regex' => 'Формат :attribute недопустим.',
    'required' => ':attribute обязателен для заполнения.',
    'required_array_keys' => ':attribute должен содержать записи для: :values.',
    'required_if' => ':attribute обязателен, когда :other равно :value.',
    'required_if_accepted' => ':attribute обязателен, когда :other принят.',
    'required_unless' => ':attribute обязателен, если :other не в :values.',
    'required_with' => ':attribute обязателен, когда :values присутствует.',
    'required_with_all' => ':attribute обязателен, когда :values присутствуют.',
    'required_without' => ':attribute обязателен, когда :values отсутствует.',
    'required_without_all' => ':attribute обязателен, когда ни одно из :values не присутствует.',
    'same' => ':attribute и :other должны совпадать.',
    'size' => [
        'array' => ':attribute должен содержать :size элементов.',
        'file' => ':attribute должен быть :size килобайт.',
        'numeric' => ':attribute должен быть :size.',
        'string' => ':attribute должен быть :size символов.',
    ],
    'starts_with' => ':attribute должен начинаться с одного из следующих: :values.',
    'string' => ':attribute должен быть строкой.',
    'timezone' => ':attribute должен быть допустимым часовым поясом.',
    'unique' => 'Такой :attribute уже существует.',
    'uploaded' => 'Не удалось загрузить :attribute.',
    'uppercase' => ':attribute должен быть в верхнем регистре.',
    'url' => ':attribute должен быть допустимым URL.',
    'ulid' => ':attribute должен быть допустимым ULID.',
    'uuid' => ':attribute должен быть допустимым UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'password' => 'пароль',
        'email' => 'электронная почта',
        'current_password' => 'текущий пароль',
        'password_confirmation' => 'подтверждение пароля',
    ],

]; 