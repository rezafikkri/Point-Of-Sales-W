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

            $transactions[$key]['created_at'] = $createdAt->toLocalizedString('dd MMM y HH:mm');

            // if created at not equal to edited at
            if ($value['created_at'] != $value['edited_at']) {
                $transactions[$key]['indo_edited_at'] = $editedAt->toLocalizedString('dd MMM y HH:mm');
            }

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
        $customerMoney = $this->transactionsModel->getOne($transactionId, 'customer_money')['customer_money'] ?? null;
        $transactionDetails = $this->transactionDetailsModel->getAll(
            $transactionId,
            'product_name, product_price, product_magnitude, product_quantity'
        );
        return json_encode([
            'customer_money' => $customerMoney,
            'transaction_details' => $transactionDetails
        ]);
    }

    public function delete()
    {
        helper('is_allowed_delete_transaction');

        // check user sign in password
        $userSignInPassword = $this->request->getPost('user_sign_in_password', FILTER_SANITIZE_STRING);        
        if (!$this->validateUserSignInPassword($userSignInPassword)) {
            return json_encode([
                'status' => 'wrong_password',
                'message' => $this->userSignInPasswordErrorMessage,
                'csrf_value' => csrf_hash()
            ]);
        }

        // delete transaction
        $transactionIds = explode(',', $this->request->getPost('transaction_ids', FILTER_SANITIZE_STRING));
        if ($this->transactionsModel->delete($transactionIds)) {
            $countTransactionId = count($transactionIds);
            $smallestEditedAt = $this->request->getPost('smallest_edited_at', FILTER_SANITIZE_STRING);
            $dateRange = $this->request->getPost('date_range', FILTER_SANITIZE_STRING);

            // if exists date_range
            if ($dateRange != null) {
                $arrDateRange = explode(' - ', $dateRange);
                $dateStart = $arrDateRange[0] . ' 00:00:00';
                $dateEnd = ($arrDateRange[1] ?? $arrDateRange[0]) . ' 23:59:59';

                // total transaction
                $totalTransaction = $this->transactionsModel->getTotalSearch($dateStart, $dateEnd);
                // get longer transaction
                $longerTransactions = $this->transactionsModel->searchLonger(
                    $countTransactionId,
                    $smallestEditedAt,
                    $dateStart,
                    $dateEnd
                );
            } else {
                // total transaction
                $totalTransaction = $this->transactionsModel->getTotal();
                // longer transaction
                $longerTransactions = $this->transactionsModel->getAllLonger($countTransactionId, $smallestEditedAt);
            }

            // convert timestamp
            foreach ($longerTransactions as $key => $value) {
                $createdAt = Time::createFromFormat('Y-m-d H:i:s', $value['created_at']);
                $editedAt = Time::createFromFormat('Y-m-d H:i:s', $value['edited_at']);

                $longerTransactions[$key]['created_at'] = $createdAt->toLocalizedString('dd MMM y HH:mm');
                $longerTransactions[$key]['indo_edited_at'] = $editedAt->toLocalizedString('dd MMM y HH:mm');

                // check permission to delete
                $longerTransactions[$key]['delete_permission'] = is_allowed_delete_transaction($value['edited_at']);
            }

            return json_encode([
                'status' => 'success',
                'longer_transactions' => $longerTransactions,
                'total_transaction' => $totalTransaction,
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

    private function createSummaryExcelFile(
        array $transactions,
        int $startRow,
        ?string $dateStart,
        ?string $dateEnd
    ): bool {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        
        // set page layout orientation
        $worksheet->getPageSetup()
                  ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        // set font default
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);

        // set width column
        $worksheet->getColumnDimension('A')->setWidth(18);
        $worksheet->getColumnDimension('B')->setWidth(18);
        $worksheet->getColumnDimension('C')->setWidth(13);
        $worksheet->getColumnDimension('D')->setWidth(24);
        $worksheet->getColumnDimension('E')->setWidth(10);
        $worksheet->getColumnDimension('F')->setWidth(20);

        // set header
        $createdAt = Time::now();
        $spreadsheet->getActiveSheet()->getHeaderFooter()
                    ->setOddHeader('&L' . $createdAt->toLocalizedString('dd MMMM y HH:mm'));

        // make title
        $worksheet->setCellValue('A1', 'Laporan Transaksi - Rangkuman');
        // merge cell for title
        $worksheet->mergeCells('A1:F1');
        // set style for title
        $styleTitle = [
            'font' => [
                'bold' => true,
                'size' => 16
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ]
        ];
        $worksheet->getStyle('A1:F1')->applyFromArray($styleTitle);
        // set row height for title
        $worksheet->getRowDimension('1')->setRowHeight(24);

        $tableHeadRow = 3;
        // if date range exists
        if ($dateStart != null) {
            // add description for transaction date range and add new row
            $dateStart = Time::createFromFormat('Y-m-d H:i:s', $dateStart, 'Asia/Jakarta');
            $dateEnd = Time::createFromFormat('Y-m-d H:i:s', $dateEnd, 'Asia/Jakarta');
            $dateStartLocalized = $dateStart->toLocalizedString('dd MMMM y');
            $dateEndLocalized = $dateEnd->toLocalizedString('dd MMMM y');

            if ($dateStartLocalized == $dateEndLocalized) {
                $dateRangeDescription = $dateStartLocalized;
            } else {
                $dateRangeDescription = $dateStartLocalized . ' - ' . $dateEndLocalized;
            }
            $worksheet->setCellValue('A2', $dateRangeDescription);

            // merge cell for description
            $worksheet->mergeCells('A2:F2');
            // set alignment
            $worksheet->getStyle('A2:F2')
                      ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            // change table head row
            $tableHeadRow = 4;
        }

        // make table head
        $worksheet->setCellValue("A$tableHeadRow", 'Dibuat');
        $worksheet->setCellValue("B$tableHeadRow", 'Diedit');
        $worksheet->setCellValue("C$tableHeadRow", 'Status');
        $worksheet->setCellValue("D$tableHeadRow", 'Kasir');
        $worksheet->setCellValue("E$tableHeadRow", "Total\nProduk");
        $worksheet->setCellValue("F$tableHeadRow", 'Total Bayaran');
        // set row height for table head
        $worksheet->getRowDimension($tableHeadRow)->setRowHeight(29);
        // set style table head
        $styleTableHead = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'horizontal' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ]
            ],
        ];
        $worksheet->getStyle("A$tableHeadRow:F$tableHeadRow")->applyFromArray($styleTableHead);

        // add transaction from db to cell
        $i = 1;
        foreach ($transactions as $t) {
            $currentRow = $startRow + $i - 1;

            $createdAt = Time::createFromFormat('Y-m-d H:i:s', $t['created_at']);
            $editedAt = Time::createFromFormat('Y-m-d H:i:s', $t['edited_at']);

            // set data to cell
            $worksheet->setCellValue("A$currentRow", $createdAt->toLocalizedString('dd-MM-y HH:mm'));
            $worksheet->setCellValue("B$currentRow", $editedAt->toLocalizedString('dd-MM-y HH:mm'));
            $worksheet->setCellValue("C$currentRow", $t['transaction_status']);
            $worksheet->setCellValue("D$currentRow", $t['full_name']);
            $worksheet->setCellValue("E$currentRow", $t['total_product']);
            $worksheet->setCellValue("F$currentRow", $t['total_payment']);

            $i++;
        }

        $tableBodyLastRow = $currentRow;
        $currentRow += 1;
        // make border for table body
        $styleTableBody = [
            'borders' => [
                'horizontal' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ]
            ]
        ];
        $worksheet->getStyle("A$startRow:F$currentRow")->applyFromArray($styleTableBody);
        // set number format to currency for table body
        $worksheet->getStyle("F$startRow:F$currentRow")->getNumberFormat()->setFormatCode('"Rp"#,##0.00');

        // make total amount of product and payment
        $worksheet->setCellValue("A$currentRow", 'Total');
        $worksheet->setCellValue("E$currentRow", "=SUM(E$startRow:E$tableBodyLastRow)");
        $worksheet->setCellValue("F$currentRow", "=SUM(F$startRow:F$tableBodyLastRow)");
        // set row height for total amount of product and payment
        $worksheet->getRowDimension($currentRow)->setRowHeight(20);

        // set style for total amount of product and payment
        $worksheet->getStyle("A$currentRow:A$currentRow")->getFont()->setBold(true);
        $styleTotalAmountProductPayment = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ];
        $worksheet->getStyle("A$currentRow:F$currentRow")->applyFromArray($styleTotalAmountProductPayment);

        $writer = new Xlsx($spreadsheet);
        $writer->save(WRITEPATH . 'export-transactions/Transactions Summary ' . date('d-m-Y H:i:s') . '.xlsx');

        return true;
    }

    public function exportExcelSummary()
    {
        $dateRange = $this->request->getPost('date_range', FILTER_SANITIZE_STRING);
    
        // if exists date_range
        if ($dateRange != null) {
            $arrDateRange = explode(' - ', $dateRange);
            $dateStart = $arrDateRange[0] . ' 00:00:00';
            $dateEnd = ($arrDateRange[1] ?? $arrDateRange[0]) . ' 23:59:59';
            
            // get transaction from db
            $transactions = $this->transactionsModel->search(0, $dateStart, $dateEnd);

            // set start row
            $startRow = 5;
        } else {
            $transactions = $this->transactionsModel->getAll(0);
            $startRow = 4;
        }

        // if transactions not exist
        if (count($transactions) <= 0) {
            return json_encode([
                'status' => 'fail',
                'message' => 'Ekspor gagal, Data transaksi tidak ada.',
                'csrf_value' => csrf_hash()
            ]);
        }

        // make excel file transactions
        $this->createSummaryExcelFile($transactions, $startRow, $dateStart ?? null, $dateEnd ?? null);
 
        return json_encode([
            'status' => 'success',
            'message' => 'Ekspor sukses, Cek file pada folder writable/export-transactions.',
            'csrf_value' => csrf_hash(),
        ]);
    }

    public function showRemaining(string $smallestEditedAt, string $dateRange = null)
    {
        helper('is_allowed_delete_transaction');

        $smallestEditedAt = filter_var($smallestEditedAt, FILTER_SANITIZE_STRING);
        $dateRange = filter_var($dateRange, FILTER_SANITIZE_STRING);

        // if exists date_range
        if ($dateRange != null) {
            $arrDateRange = explode(' - ', $dateRange);

            $dateStart = $arrDateRange[0] . ' 00:00:00';
            $dateEnd = ($arrDateRange[1] ?? $arrDateRange[0]) . ' 23:59:59';

            // total transaction
            $totalTransaction = $this->transactionsModel->getTotalSearch($dateStart, $dateEnd);
            // get longer transaction
            $longerTransactions = $this->transactionsModel->searchLonger(
                0,
                $smallestEditedAt,
                $dateStart,
                $dateEnd
            );
        } else {
            // total transaction
            $totalTransaction = $this->transactionsModel->getTotal();
            // longer transaction
            $longerTransactions = $this->transactionsModel->getAllLonger(0, $smallestEditedAt);
        }

        // convert timestamp
        foreach ($longerTransactions as $key => $value) {
            $createdAt = Time::createFromFormat('Y-m-d H:i:s', $value['created_at']);
            $editedAt = Time::createFromFormat('Y-m-d H:i:s', $value['edited_at']);

            $longerTransactions[$key]['created_at'] = $createdAt->toLocalizedString('dd MMM y HH:mm');
            $longerTransactions[$key]['indo_edited_at'] = $editedAt->toLocalizedString('dd MMM y HH:mm');

            // check permission to delete
            $longerTransactions[$key]['delete_permission'] = is_allowed_delete_transaction($value['edited_at']);
        }

        return json_encode([
            'status' => 'success',
            'longer_transactions' => $longerTransactions,
            'total_transaction' => $totalTransaction,
            'transaction_limit' => static::TRANSACTION_LIMIT,
            'csrf_value' => csrf_hash()
        ]);
    }

    private function createDetailsExcelFile(
        array $transactions,
        int $lastRow,
        ?string $dateStart,
        ?string $dateEnd
    ): bool {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        // set font default
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);

        // set width column
        $worksheet->getColumnDimension('A')->setWidth(20);
        $worksheet->getColumnDimension('B')->setWidth(10);
        $worksheet->getColumnDimension('C')->setWidth(19);
        $worksheet->getColumnDimension('D')->setWidth(8);
        $worksheet->getColumnDimension('E')->setWidth(19);
       
        // set header
        $createdAt = Time::now();
        $spreadsheet->getActiveSheet()->getHeaderFooter()
                                      ->setOddHeader('&L' . $createdAt->toLocalizedString('dd MMMM y HH:mm'));

        // make title
        $worksheet->setCellValue('A1', 'Laporan Transaksi - Rincian');
        // merge cell for title
        $worksheet->mergeCells('A1:E1');
        // set style for title
        $styleTitle = [
            'font' => [
                'bold' => true,
                'size' => 16
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ]
        ];
        $worksheet->getStyle('A1:E1')->applyFromArray($styleTitle);
        // set row height for title
        $worksheet->getRowDimension('1')->setRowHeight(24);

        // if date range exists
        if ($dateStart != null) {
            // add description for transaction date range and add new row
            $dateStart = Time::createFromFormat('Y-m-d H:i:s', $dateStart, 'Asia/Jakarta');
            $dateEnd = Time::createFromFormat('Y-m-d H:i:s', $dateEnd, 'Asia/Jakarta');
            $dateStartLocalized = $dateStart->toLocalizedString('dd MMMM y');
            $dateEndLocalized = $dateEnd->toLocalizedString('dd MMMM y');

            if ($dateStartLocalized == $dateEndLocalized) {
                $dateRangeDescription = $dateStartLocalized;
            } else {
                $dateRangeDescription = $dateStartLocalized . ' - ' . $dateEndLocalized;
            }
            $worksheet->setCellValue('A2', $dateRangeDescription);

            // merge cell for description
            $worksheet->mergeCells('A2:E2');
            // set alignment
            $worksheet->getStyle('A2:E2')
                      ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }
        
        // add transaction from db to cell
        $i = 0;
        $lastTransactionId = '';
        $countTransaction = count($transactions);
        $totalProductCells = '';
        $totalPaymentCells = '';
        foreach ($transactions as $t) {
            // if last transaction id != current transaction id
            if ($lastTransactionId != $t['transaction_id']) {
                // change last transaction id
                $lastTransactionId = $t['transaction_id'];

                $headerRow1 = $lastRow + 2;
                $headerRow2 = $lastRow + 3;
                $tableHeadRow = $lastRow + 4;
    
                $createdAt = Time::createFromFormat('Y-m-d H:i:s', $t['created_at']);
                $editedAt = Time::createFromFormat('Y-m-d H:i:s', $t['edited_at']);
                
                // set header to cell
                $worksheet->setCellValue("A$headerRow1", 'Kasir : ' . $t['full_name']);
                $worksheet->setCellValue("A$headerRow2", 'Status : ' . $t['transaction_status']);
                $worksheet->setCellValue("D$headerRow1", 'Dibuat : ' . $createdAt->toLocalizedString('dd-MM-y HH:mm'));
                $worksheet->setCellValue("D$headerRow2", 'Diedit : ' . $editedAt->toLocalizedString('dd-MM-y HH:mm'));
                // merge cell for header
                $worksheet->mergeCells("A$headerRow1:C$headerRow1");
                $worksheet->mergeCells("A$headerRow2:C$headerRow2");
                $worksheet->mergeCells("D$headerRow1:E$headerRow1");
                $worksheet->mergeCells("D$headerRow2:E$headerRow2");

                // make table head
                $worksheet->setCellValue("A$tableHeadRow", 'Nama Produk');
                $worksheet->setCellValue("B$tableHeadRow", 'Besaran');
                $worksheet->setCellValue("C$tableHeadRow", 'Harga');
                $worksheet->setCellValue("D$tableHeadRow", 'Jumlah');
                $worksheet->setCellValue("E$tableHeadRow", 'Bayaran');   
                // set row height for table head
                $worksheet->getRowDimension($tableHeadRow)->setRowHeight(20);
                // set style table head
                $styleTableHead = [
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                        'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ]
                    ],
                ];
                $worksheet->getStyle("A$tableHeadRow:E$tableHeadRow")->applyFromArray($styleTableHead);

                // change last row
                $lastRow += 5;
                // set table body first row
                $tableBodyFirstRow = $lastRow;
            } else {
                // change last row
                $lastRow++;
            }

            // set data to cell
            $worksheet->setCellValue("A$lastRow", $t['product_name']);
            $worksheet->setCellValue("B$lastRow", $t['product_magnitude']);
            $worksheet->setCellValue("C$lastRow", $t['product_price']);
            $worksheet->setCellValue("D$lastRow", $t['product_quantity']);
            $worksheet->setCellValue("E$lastRow", "=C$lastRow*D$lastRow");
    
            // make border for table body
            $styleTableBody = [
                'borders' => [
                    'horizontal' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                    'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                    'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ]
                ]
            ];
            $worksheet->getStyle("A$lastRow:E$lastRow")->applyFromArray($styleTableBody);

            $i++;
            // if $i <= count transaction - 1 and if last transaction id != next transaction id
            if ($i == $countTransaction || $lastTransactionId != $transactions[$i]['transaction_id']) {
                $tableBodyLastRow = $lastRow;
                $lastRow++;
                // set total to cell
                $worksheet->setCellValue("A$lastRow", 'Total');
                $worksheet->setCellValue("D$lastRow", "=SUM(D$tableBodyFirstRow:D$tableBodyLastRow)");
                $worksheet->setCellValue("E$lastRow", "=SUM(E$tableBodyFirstRow:E$tableBodyLastRow)");
                $worksheet->mergeCells("A$lastRow:C$lastRow");

                // note total product quantity and and total payment cell
                $totalProductCells .= 'D' . $lastRow . ',';
                $totalPaymentCells .= 'E' . $lastRow . ',';

                // set style total
                $worksheet->getStyle("A$lastRow:C$lastRow")->getFont()->setBold(true);
                $styleTotal = [
                    'borders' => [
                        'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                        'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ]
                    ]
                ];
                $worksheet->getStyle("A$lastRow:E$lastRow")->applyFromArray($styleTotal);
                // set number format to currency for product price, payment and total payment
                $worksheet->getStyle("C$tableBodyFirstRow:C$tableBodyLastRow")
                          ->getNumberFormat()->setFormatCode('"Rp"#,##0.00');
                $worksheet->getStyle("E$tableBodyFirstRow:E$tableBodyLastRow")
                          ->getNumberFormat()->setFormatCode('"Rp"#,##0.00');
                $worksheet->getStyle("E$lastRow")->getNumberFormat()->setFormatCode('"Rp"#,##0.00');
            }
        }

        // make total amount of product and payment
        $rowTotalAmountOfProduct = $lastRow + 2;
        $rowTotalAmountOfPayment = $lastRow + 3;

        $worksheet->setCellValue("A$rowTotalAmountOfProduct", 'Jumlah Total Produk');
        $worksheet->setCellValue("A$rowTotalAmountOfPayment", 'Jumlah Total Bayaran');

        $worksheet->setCellValue("C$rowTotalAmountOfProduct", "=SUM($totalProductCells)");
        $worksheet->setCellValue("C$rowTotalAmountOfPayment", "=SUM($totalPaymentCells)");

        $worksheet->mergeCells("A$rowTotalAmountOfProduct:B$rowTotalAmountOfProduct");
        $worksheet->mergeCells("A$rowTotalAmountOfPayment:B$rowTotalAmountOfPayment");
        $worksheet->mergeCells("C$rowTotalAmountOfProduct:E$rowTotalAmountOfProduct");
        $worksheet->mergeCells("C$rowTotalAmountOfPayment:E$rowTotalAmountOfPayment");
        
        // set style for total amount of product and payment
        $worksheet->getStyle("A$rowTotalAmountOfProduct:A$rowTotalAmountOfPayment")->getFont()->setBold(true);
        $styleTotalAmountProductPayment = [
            'borders' => [
                'horizontal' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                'bottom' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ]
            ]
        ];
        $worksheet->getStyle("A$rowTotalAmountOfProduct:E$rowTotalAmountOfPayment")->applyFromArray($styleTotalAmountProductPayment);
        // set number format to currency
        $worksheet->getStyle("C$rowTotalAmountOfPayment")->getNumberFormat()->setFormatCode('"Rp"#,##0.00');

        $writer = new Xlsx($spreadsheet);
        $writer->save(WRITEPATH . 'export-transactions/Transaction Details ' . date('d-m-Y H:i:s') . '.xlsx');

        return true;
    }

    public function exportExcelDetails()
    {
        $dateRange = $this->request->getPost('date_range', FILTER_SANITIZE_STRING);
    
        // if exists date_range
        if ($dateRange != null) {
            $arrDateRange = explode(' - ', $dateRange);
            $dateStart = $arrDateRange[0] . ' 00:00:00';
            $dateEnd = ($arrDateRange[1] ?? $arrDateRange[0]) . ' 23:59:59';
            
            // get transaction from db
            $transactions = $this->transactionsModel->searchDetails(0, $dateStart, $dateEnd);

            // set last row
            $lastRow = 2;
        } else {
            $transactions = $this->transactionsModel->getAllDetails(0);
            $lastRow = 1;
        }

        // if transactions not exist
        if (count($transactions) <= 0) {
            return json_encode([
                'status' => 'fail',
                'message' => 'Ekspor gagal, Data transaksi tidak ada.',
                'csrf_value' => csrf_hash()
            ]);
        }

        // make excel file transactions
        $this->createDetailsExcelFile($transactions, $lastRow, $dateStart ?? null, $dateEnd ?? null);
 
        return json_encode([
            'status' => 'success',
            'message' => 'Ekspor sukses, Cek file pada folder writable/export-transactions.',
            'csrf_value' => csrf_hash(),
            'transactions' => $transactions
        ]);
    }
}
