<?php

namespace App\Libraries;

/**
 * POSW custom rules for validation
 */
class POSWRules
{
    public function __construct()
    {
        $this->request = \Config\Services::request();
    }

    public function product_price(string $str, ?array &$errors): bool
    {
        $prices = $this->request->getPost('product_prices');

        $errors = [];
        $countPrice = count($prices);
        for ($i = 0; $i < $countPrice; $i++) {
            // if empty price
            if (empty(trim($prices[$i]))) {
                $errors[$i] = lang('Validation.required', [
                    'field' => 'Harga'
                ]);
            }
            // if price more than 10 character
            elseif (strlen($prices[$i]) > 10) {
                $errors[$i] = lang('Validation.max_length', [
                    'field' => 'Harga',
                    'param' => 10
                ]);
            }
            // if price not a number
            elseif (!preg_match('/^\d+$/', $prices[$i])) {
                $errors[$i] = lang('Validation.is_natural', [
                    'field' => 'Harga'
                ]);
            }
        }

        if (count($errors) > 0) {
            return false;
        }
        return true;
    }

    public function product_magnitude(string $str, ?array &$errors): bool
    {
        $magnitudes = $this->request->getPost('product_magnitudes');

        $errors = [];
        $countMagnitude = count($magnitudes);
        for ($i = 0; $i < $countMagnitude; $i++) {
            // if empty magnitude
            if (empty(trim($magnitudes[$i]))) {
                $errors[$i] = lang('Validation.required', [
                    'field' => 'Besaran'
                ]);
            }
            // if magnitude more than 20 character
            elseif (strlen($magnitudes[$i]) > 20) {
                $errors[$i] = lang('Validation.max_length', [
                    'field' => 'Besaran',
                    'param' => 20
                ]);
            }
        }

        if (count($errors) > 0) {
            return false;
        }
        return true;
    }

    public function product_photo(?string $str, ?string &$error = null): bool
    {
        $file = $this->request->getFile('product_photo');

        // if not file was uploaded
        if ($file->getError() == 4) {
            $error = "Tidak ada Foto Produk yang diupload.";
        }
        // if not valid file
        elseif (!$file->isValid()) {
            $error = "Foto Produk yang diupload tidak benar.";
        }
        // if size file exceed 1MB
        elseif ($file->getSizeByUnit('mb') > 1) {
            $error = "Ukuran Foto Produk tidak bisa melebihi 1MB.";
        }
        // if file extension not jpg or jpeg
        elseif (strtolower($file->getExtension()) != 'jpg' && strtolower($file->getExtension()) != 'jpeg') {
            $error = "Ekstensi Foto Produk harus .jpg atau .jpeg!";
        }

        // if $error != null
        if ($error) {
            return false;
        }

        return true;
    }
}
