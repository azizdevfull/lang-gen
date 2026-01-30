<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Conflict Policy (To'qnashuv siyosati)
    |--------------------------------------------------------------------------
    |
    | Agar 'auth.password' oldindan string bo'lsa ('Parol xato'), lekin siz
    | kodda 'auth.password.min' deb array ishlatgan bo'lsangiz nima qilish kerak?
    |
    | 'preserve'  - Eski stringni saqlab qoladi (Yangi kalit yozilmaydi).
    | 'overwrite' - Eski stringni o'chirib, o'rniga array yaratadi.
    |
    */
    'conflict_policy' => 'overwrite', // yoki 'preserve'

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    */
    'default_lang' => 'en',
];