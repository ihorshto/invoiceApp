<?php

return [
    'title'          => 'Factures',
    'new'            => 'Nouvelle facture',
    'edit'           => 'Modifier facture',
    'search'         => 'N° ou client…',
    'confirm_delete' => 'Supprimer cette facture ?',
    'confirm_paid'   => 'Marquer comme payée ?',
    'empty'          => 'Aucune facture',

    'statuses' => [
        'draft'     => 'Brouillon',
        'sent'      => 'Envoyée',
        'paid'      => 'Payée',
        'overdue'   => 'En retard',
        'cancelled' => 'Annulée',
    ],

    'filter' => [
        'all_statuses' => 'Tous statuts',
    ],

    'fields' => [
        'number'      => 'N°',
        'client'      => 'Client',
        'date'        => 'Date',
        'due_date'    => 'Échéance',
        'issue_date'  => 'Date d\'émission',
        'total'       => 'Total TTC',
        'status'      => 'Statut',
        'currency'    => 'Devise',
        'items'       => 'Lignes',
        'notes'       => 'Notes',
        'footer'      => 'Pied de page',
        'subtotal'    => 'Sous-total HT',
        'vat'         => 'TVA',
        'product'     => 'Produit',
        'description' => 'Description',
        'unit_price'  => 'Prix U.',
        'quantity'    => 'Qté',
        'vat_rate'    => 'TVA %',
        'line_total'  => 'Total HT',
    ],

    'action' => [
        'add_line'    => '+ Ajouter ligne',
        'mark_paid'   => 'Marquer payée',
        'view'        => 'Voir',
    ],

    'select_client'  => '— Sélectionner —',
    'select_product' => '— Produit —',
];
