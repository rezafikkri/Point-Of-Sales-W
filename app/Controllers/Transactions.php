<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\{TransactionsModel, UsersModel, TransactionDetailsModel};
use PhpOffice\PhpSpreadsheet\{Spreadsheet, Writer\Xlsx};
use CodeIgniter\I18n\Time;

class Transactions extends BaseController
{
    private const TRANSACTION_LIMIT = 50;

    public function __construct()
    {
        $this->transactionsModel = new TransactionsModel();
        $this->transactionDetailsModel = new TransactionDetailsModel();
        $this->usersModel = new UsersModel();
    }

    public function index()
    {
        helper(['active_menu', 'is_allowed_delete_transaction']);

        $data['title'] = 'Transaksi . POSW';

        $data['totalTransaction'] = $this->transactionsModel->getTotal();
        $data['transactions'] = $this->transactionsModel->getAll(static::TRANSACTION_LIMIT);
        $data['transactionLimit'] = static::TRANSACTION_LIMIT;

        return view('transactions', $data);
    }

    public function search(string $dateRange)
    {
        helper('is_allowed_delete_transaction');

        $arrDateRange = explode(' - ', filter_var($dateRange, FILTER_SANITIZE_STRING));
        $dateStart = $arrDateRange[0] . ' 00:00:00';
        $dateEnd = ($arrDateRange[1] ?? $arrDateRange[0]) . ' 23:59:59';

        // get total transaction search and transactions search 
        $totalTransaction = $this->transactionsModel->getTotalSearch($dateStart, $dateEnd);
        $transactions = $this->transactionsModel->search(static::TRANSACTION_LIMIT, $dateStart, $dateEnd);

        // convert timestamp
        foreach ($transactions as $key => $value) {
            $createdAt = Time::createFromFormat('Y-m-d H:i:s', $value['created_at']);
            $editedAt = Time::createFromFormat('Y-m-d H:i:s', $value['edited_at']);

            $transactions[$key]['created_at'] = $createdAt->toLocalizedString('dd MMM yyyy HH:mm');
            $transactions[$key]['indo_edited_at'] = $editedAt->toLocalizedString('dd MMM yyyy HH:mm');

            // check permission to delete
            $transactions[$key]['delete_permission'] = is_allowed_delete_transaction($value['edited_at']);
        }

        return json_encode([
            'transactions' => $transactions,
            'total_transaction' => $totalTransaction,
            'transaction_limit' => static::TRANSACTION_LIMIT,
            'csrf_value' => csrf_hash()
        ]);
    }

    public function showDetails(string $transactionId)
    {
        $transactionId = filter_var($transactionId, FILTER_SANITIZE_STRING);
        $transactionDetails = $this->transactionDetailsModel->getAll(
            $transactionId,
            'product_name, product_price, product_magnitude, product_quantity'
        );
        return json_encode(['transaction_details' => $transactionDetails]);
    }

    public function removeTransactionsInDB()
    {
        // check password sign in user
        $password = $this->request->getPost('password', FILTER_SANITIZE_STRING);
        $password_db = $this->user_model->findUser($_SESSION['posw_user_id'], 'password')['password'];
        $check_password = check_password_sign_in_user($password, $password_db);
        if ($check_password !== 'yes') {
            return json_encode([
                'status' => 'wrong_password',
                'message' => $check_password,
                'csrf_value' => csrf_hash()
            ]);
        }

        // remove transaction
        $transaction_ids = explode(',', $this->request->getPost('transaction_ids', FILTER_SANITIZE_STRING));
        if ($this->transaction_model->removeTransactions($transaction_ids) > 0) {
            $count_transaction_id = count($transaction_ids);
            $smallest_create_time = $this->request->getPost('smallest_create_time');
            $date_range = $this->request->getPost('date_range', FILTER_SANITIZE_STRING);

            // if exists date_range
            if ($date_range !== null) {
                $arr_date_range = explode(' - ', $date_range);
                $date_start = $arr_date_range[0].' 00:00:00';
                $date_end = ($arr_date_range[1] ?? $arr_date_range[0]).' 23:59:59';

                // transaction total
                $transaction_total = $this->transaction_model->countAllTransactionSearch($date_start, $date_end);
                // get longer transaction
                $longer_transactions = $this->transaction_model->getLongerTransactionSearches(
                    $count_transaction_id,
                    $smallest_create_time,
                    $date_start,
                    $date_end
                );

            } else {
                // transaction total
                $transaction_total = $this->transaction_model->countAllTransaction();
                // longer transaction
                $longer_transactions = $this->transaction_model->getLongerTransactions($count_transaction_id, $smallest_create_time);
            }

            $count_longer_transactions = count($longer_transactions);
            for ($i = 0; $i < $count_longer_transactions; $i++) {
                // convert timestamp
                $longer_transactions[$i]['indo_create_time'] = $this->indo_time->toIndoLocalizedString($longer_transactions[$i]['waktu_buat']);
                // generate permission for delete
                $longer_transactions[$i]['permission_delete'] = is_transaction_allowed_delete(
                    $longer_transactions[$i]['waktu_buat'],
                    $longer_transactions[$i]['status_transaksi']
                );
            }

            return json_encode([
                'status' => 'success',
                'longer_transactions' => $longer_transactions,
                'transaction_total' => $transaction_total,
                'transaction_limit' => static::TRANSACTION_LIMIT,
                'csrf_value' => csrf_hash()
            ]);
        }

        return json_encode([
            'status' => 'fail',
            'message' => 'Gagal menghapus transaksi.',
            'csrf_value' => csrf_hash()
        ]);
    }

    private function makeTransactionExcelFile(
        array $transactions,
        int $startRow,
        ? string $dateRange,
        ? string $dateStart,
        ? string $dateEnd
    ): bool {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        // set font default
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);

        // set width column
        $worksheet->getColumnDimension('A')->setWidth(4);
        $worksheet->getColumnDimension('B')->setWidth(13);
        $worksheet->getColumnDimension('C')->setWidth(14);
        $worksheet->getColumnDimension('D')->setWidth(7);
        $worksheet->getColumnDimension('E')->setWidth(18);
        $worksheet->getColumnDimension('F')->setWidth(19);

        // set height row
        $worksheet->getRowDimension('7')->setRowHeight(25);

        // make title
        $worksheet->setCellValue('A1', 'Laporan Transaksi');
        $worksheet->setCellValue('A4', 'Diekspor oleh '.$_SESSION['posw_user_full_name']);
        $worksheet->setCellValue('A5', 'Pada '.$this->indo_time->toIndoLocalizedString(date('Y-m-d H:i:s')));

        // merge cell for title
        $worksheet->mergeCells('A1:F1');
        $worksheet->mergeCells('A4:F4');
        $worksheet->mergeCells('A5:F5');

        // set style for title
        $styleArrayTitle = [
            'font' => [
                'bold' => true,
                'size' => 16
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $worksheet->getStyle('A1:F1')->applyFromArray($styleArrayTitle);

        // make table head
        $worksheet->setCellValue('A7', 'No');
        $worksheet->setCellValue('B7', 'Total Produk');
        $worksheet->setCellValue('C7', 'Total Bayaran');
        $worksheet->setCellValue('D7', 'Status');
        $worksheet->setCellValue('E7', 'Kasir');
        $worksheet->setCellValue('F7', 'Waktu Buat');

        // set style table head
        $styleArrayTableHead = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
            ],
        ];
        $worksheet->getStyle('A7:F7')->applyFromArray($styleArrayTableHead);

        // if date range exists
        if ($dateRange !== null) {
            // add description for transaction date range and add new row
            $formatDateDescription = 'dd MMMM YYYY';
            $dateStartDescription = $this->indo_time->toIndoLocalizedString($dateStart, $formatDateDescription);
            $dateEndDescripton = $this->indo_time->toIndoLocalizedString($dateEnd, $formatDateDescription);

            $worksheet->setCellValue('A6', "Transaksi dari $dateStartDescription sampai $dateEndDescripton");
            $worksheet->insertNewRowBefore(7, 1);
            $worksheet->mergeCells('A6:F6');
        }

        // add transaction from db to cell
        $i = 1;
        foreach ($transactions as $t) {
            $currentRow = $startRow + $i - 1;

            // set row height
            $worksheet->getRowDimension($currentRow)->setRowHeight(20);

            // set data to cell
            $worksheet->setCellValue("A$currentRow", $i);
            $worksheet->setCellValue("B$currentRow", $t['product_total']);
            $worksheet->setCellValue("C$currentRow", $t['payment_total']);
            $worksheet->setCellValue("D$currentRow", $t['status_transaksi']);
            $worksheet->setCellValue("E$currentRow", $t['nama_lengkap']);
            $worksheet->setCellValue("F$currentRow", $t['waktu_buat']);

            $i++;
        }

        // set alignment for table data
        $worksheet->getStyle("A$startRow:A$currentRow")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // make border for table data
        $styleArrayTableData = [
            'borders' => [
                'inside' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                'left' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
            ],
        ];
        $worksheet->getStyle("A$startRow:F$currentRow")->applyFromArray($styleArrayTableData);

        // make amount of product total and payment total
        $rowAmountOfProductTotal = $currentRow + 2;
        $rowAmountOfPaymentTotal = $currentRow + 3;

        $worksheet->getRowDimension($rowAmountOfProductTotal)->setRowHeight(20);
        $worksheet->getRowDimension($rowAmountOfPaymentTotal)->setRowHeight(20);

        $worksheet->setCellValue("A$rowAmountOfProductTotal", 'Jumlah Total Produk');
        $worksheet->setCellValue("A$rowAmountOfPaymentTotal", 'Jumlah Total Bayaran');

        $worksheet->setCellValue("D$rowAmountOfProductTotal", "=SUM(B$startRow:B$currentRow)");
        $worksheet->setCellValue("D$rowAmountOfPaymentTotal", "=SUM(C$startRow:C$currentRow)");

        $worksheet->mergeCells("A$rowAmountOfProductTotal:C$rowAmountOfProductTotal");
        $worksheet->mergeCells("A$rowAmountOfPaymentTotal:C$rowAmountOfPaymentTotal");
        $worksheet->mergeCells("D$rowAmountOfProductTotal:F$rowAmountOfProductTotal");
        $worksheet->mergeCells("D$rowAmountOfPaymentTotal:F$rowAmountOfPaymentTotal");

        // set style for amount of product total and payment total
        $styleArrayAmountProductPaymentTotalHead = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
            ],
        ];
        $worksheet->getStyle("A$rowAmountOfProductTotal:A$rowAmountOfPaymentTotal")->applyFromArray($styleArrayAmountProductPaymentTotalHead);

        $styleArrayAmountProductPaymentTotalData = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
            ],
        ];
        $worksheet->getStyle("B$rowAmountOfProductTotal:F$rowAmountOfPaymentTotal")->applyFromArray($styleArrayAmountProductPaymentTotalData);

        $writer = new Xlsx($spreadsheet);
        $writer->save(WRITEPATH.'transaction_exports/Transaksi '.date('d m Y H:i:s').'.xlsx');

        return true;
    }

    public function exportTransactionsToExcel()
    {
        $dateRange = $this->request->getPost('date_range', FILTER_SANITIZE_STRING);

        // if exists date_range
        if ($dateRange !== null) {
            $arrDateRange = explode(' - ', $dateRange);
            $dateStart = $arrDateRange[0].' 00:00:00';
            $dateEnd = ($arrDateRange[1] ?? $arrDateRange[0]).' 23:59:59';

            // get transaction from db
            $transactions = $this->transaction_model->getTransactionSearches(0, $dateStart, $dateEnd);

            // set start row
            $startRow = 9;
        } else {
            $transactions = $this->transaction_model->getTransactions(static::TRANSACTION_LIMIT);
            $startRow = 8;
        }

        // if not exists transactions
        if (count($transactions) <= 0) {
            return json_encode(['status' => 'fail', 'message' => 'Ekspor gagal, Data transaksi tidak ada.', 'csrf_value' => csrf_hash()]);
        }

        // make transaction excel file
        $this->makeTransactionExcelFile($transactions, $startRow, $dateRange, $dateStart ?? null, $dateEnd ?? null);

        return json_encode([
            'status' => 'success',
            'message' => 'Ekspor sukses, Cek file pada folder writable/transaction_exports.',
            'csrf_value' => csrf_hash(),
        ]);
    }
}
