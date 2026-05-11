<?php

return [
    'title'          => 'Рахунки',
    'new'            => 'Новий рахунок',
    'edit'           => 'Редагувати рахунок',
    'search'         => 'N° або клієнт…',
    'confirm_delete' => 'Видалити рахунок?',
    'confirm_paid'   => 'Позначити оплаченим?',
    'empty'          => 'Рахунків немає',

    'statuses' => [
        'draft'     => 'Чернетка',
        'sent'      => 'Відправлено',
        'paid'      => 'Оплачено',
        'overdue'   => 'Прострочено',
        'cancelled' => 'Скасовано',
    ],

    'filter' => [
        'all_statuses' => 'Усі статуси',
    ],

    'fields' => [
        'number'      => '№',
        'client'      => 'Клієнт',
        'date'        => 'Дата',
        'due_date'    => 'Термін',
        'issue_date'  => 'Дата виставлення',
        'total'       => 'Загальна сума',
        'status'      => 'Статус',
        'currency'    => 'Валюта',
        'items'       => 'Рядки',
        'notes'       => 'Нотатки',
        'footer'      => 'Нижній колонтитул',
        'subtotal'    => 'Підсумок без ПДВ',
        'vat'         => 'ПДВ',
        'product'     => 'Товар',
        'description' => 'Опис',
        'unit_price'  => 'Ціна',
        'quantity'    => 'К-сть',
        'vat_rate'    => 'ПДВ %',
        'line_total'  => 'Сума',
    ],

    'action' => [
        'add_line'       => '+ Додати рядок',
        'mark_paid'      => 'Оплачено',
        'change_status'  => 'Змінити статус',
        'view'           => 'Огляд',
    ],

    'select_client'  => '— Обрати —',
    'select_product' => '— Товар —',
];
