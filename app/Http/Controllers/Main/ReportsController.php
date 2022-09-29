<?php

namespace App\Http\Controllers\Main;

use App\Exports\RetailExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MsSalesArea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function thruput()
    {
        $salesAreas = MsSalesArea::all();
        return view('main.reports.thruput')
                ->with('salesAreas', $salesAreas);
    }

    public function retail()
    {
        return view('main.reports.retail');
    }

    public function salesman()
    {
        return view('main.reports.salesman');
    }

    public function downloadThrupt(Request $request)
    {
        $salesAreas = $request->salesArea;
        $yearMonth = $request->month;

        $companies = implode(",", Auth::user()->companies);
        $salesAreasStr = implode(",", $salesAreas);
        $year = explode("-", $yearMonth)[0];
        $month = explode("-", $yearMonth)[1];

        // $results = DB::select("SELECT ur.name as salesAgent, IFNULL(salesArea,'UNKNOWN') as salesArea, monthTarget FROM ms_sales_people as sp inner Join users as ur on sp.userId = ur.id WHERE ur.status <> '3' AND EXISTS (SELECT 1 FROM users as u INNER JOIN user_roles as r on u.id = r.user_id WHERE u.username = ur.username and r.role_id in ('4','5'))");

        $sales = DB::select("SELECT
                                    IFNULL(lefttbl.salesArea, 'UNKNOWN') AS salesArea,
                                    lefttbl.saleDiv,
                                    lefttbl.companyCode,
                                    lefttbl.name AS salesAgent,
                                    DAY(lefttbl.docDate) AS docDate,
                                    DAYOFWEEK(lefttbl.docDate) AS DAYOFWEEK,
                                    righttbl.amt,
                                    monthTarget
                                FROM
                                    (
                                        SELECT sp.salesArea, 'SALE DEVISION' AS saleDiv, c.code AS companyCode, ur.name AS NAME, sa.salesAgent, dts.dt AS docDate, sa.customerId, sp.monthTarget
                                        FROM users AS ur
                                        INNER JOIN ms_sales_people AS sp ON sp.userId = ur.id
                                        INNER JOIN ms_sales_person_mappings AS spm ON sp.id = spm.salespersonId
                                        INNER JOIN ac_sales_agents AS sa ON spm.acSalesAgentId = sa.id
                                        INNER JOIN ms_companies AS c ON sa.customerId = c.extId
                                        INNER JOIN (
                                            SELECT a.Date AS dt
                                            FROM (
                                                SELECT last_day('$year-$month-01') - INTERVAL(a.a +(10 * b.a) +(100 * c.a)) DAY AS DATE
                                                FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 ) AS a
                                                        CROSS JOIN(SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 ) AS b
                                                        CROSS JOIN(SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 ) AS c
                                                    ) AS a
                                                WHERE a.Date BETWEEN '$year-$month-01' AND last_day('$year-$month-01')
                                                ORDER BY a.Date
                                        ) AS dts
                                        WHERE
                                            ur.status <> '3' AND spm.companyId IN($companies) AND EXISTS(SELECT 1 FROM users AS u INNER JOIN user_roles AS r ON u.id = r.user_id INNER JOIN roles AS p ON r.role_id = p.id WHERE u.username = ur.username AND p.id IN('4', '5'))
                                    ) AS lefttbl
                                LEFT JOIN(
                                    SELECT CAST(so.finalTotal AS DECIMAL(25, 2)) AS amt, so.docDate, so.customerId, so.salesAgent
                                    FROM ac_sales_invoices AS so
                                    WHERE
                                        YEAR(so.docDate) = '$year' AND MONTH(so.docDate) = '$month' AND so.deleted = 0 UNION ALL
                                        SELECT CAST(so.finalTotal * -1 AS DECIMAL(25, 2)) AS amt, so.docDate, so.customerId, so.salesAgent FROM ac_sales_credit_notes AS so WHERE YEAR(so.docDate) = '$year' AND MONTH(so.docDate) = '$month' AND so.deleted = 0 UNION ALL
                                        SELECT CAST(so.netTotal AS DECIMAL(25, 2)) AS amt, so.docDate, so.customerId, so.salesAgent FROM ac_sales_debit_notes AS so WHERE YEAR(so.docDate) = '$year' AND MONTH(so.docDate) = '$month' AND so.deleted = 0
                                ) AS righttbl
                                ON lefttbl.salesAgent = righttbl.salesAgent AND lefttbl.docDate = righttbl.docDate AND lefttbl.customerId = righttbl.customerId
                                WHERE lefttbl.salesArea IN($salesAreasStr)");

        $thruputAchivementReportData = [];
        foreach($sales as $sale)
        {
            $data = [];
            $data['salesArea'] = $sale[0];
            $data['saleDiv'] = $sale[1];
            $data['companyCode'] = $sale[2];
            $data['salesAgent'] = $sale[3];
            $data['docDate'] = $sale[4];
            $data['dayOfWeek'] = $sale[5];
            $data['amt'] = $sale[6];
            $data['monthTarget'] = $sale[7];
            array_push($thruputAchivementReportData, $data);
        }

        $salesTargetReportData = DB::select("SELECT ur.name as salesAgent, IFNULL(salesArea,'UNKNOWN') as salesArea, monthTarget FROM ms_sales_people AS sp inner Join users AS ur on sp.userId = ur.id WHERE ur.status <> '3' AND EXISTS (SELECT 1 FROM users AS u INNER JOIN user_roles AS r on u.id = r.user_id INNER JOIN roles AS rp on r.role_id = rp.id WHERE u.username = ur.username and rp.id in ('3','4'))");

        $targetBySaleAgent = [];
        foreach($salesTargetReportData as $row)
        {
            $data = [];
            array_push($data, $row->salesAgent);
            array_push($data, $row->monthTarget);
            array_push($targetBySaleAgent, $data);
        }

        print_r($targetBySaleAgent);
        exit;

    }


    public function downloadRetail(Request $request)
    {
        $yearMonth = $request->month;

        $companies = Auth::user()->company_ids;

        $year = explode("-", $yearMonth)[0];
        $month = explode("-", $yearMonth)[1];

        $results = DB::select("SELECT
                                    l_tbl.name AS salesAgent,
                                    CASE WHEN l_tbl.managerName IS NULL THEN l_tbl.name ELSE l_tbl.managerName END AS managerName,
                                    l_tbl.id,
                                    l_tbl.rptCategory,
                                    '$yearMonth' AS strMonth,
                                    CAST(SUM(r_tbl.weight) / 1000 AS DECIMAL(25, 2)) AS weight,
                                    SUM(r_tbl.amt) AS amt
                                FROM
                                    ( SELECT ur.name AS NAME, sa.salesAgent, manager.name AS managerName, sa.customerId, rpt.itemCode AS itemCode, cat.id, CONCAT(rpt.rptCategory, '(', CAST(cat.targetWeight AS CHAR), cat.targetUOM, ')' ) AS rptCategory
                                    FROM users AS ur
                                        INNER JOIN ms_sales_people AS sp ON sp.userId = ur.id
                                        INNER JOIN ms_sales_person_mappings AS spm ON sp.id = spm.salespersonId
                                        INNER JOIN ac_sales_agents sa ON spm.acSalesAgentId = sa.id
                                        INNER JOIN ms_companies AS c ON sa.customerId = c.extId
                                        INNER JOIN rpt_category_mappings AS rpt ON sa.customerId = rpt.companyId
                                        INNER JOIN rpt_categories AS cat ON rpt.rptCategory = cat.name
                                        LEFT JOIN users AS manager ON sp.managerId = manager.id
                                    WHERE
                                        ur.status <> '3' AND spm.companyId IN($companies)
                                        AND ur.username NOT IN(SELECT login_name FROM rpt_excluded_users)
                                        AND EXISTS(SELECT 1
                                                FROM users AS u
                                            INNER JOIN user_roles AS r ON u.id = r.user_id
                                            INNER JOIN roles AS p ON r.role_id = p.id
                                        WHERE u.username = ur.username AND p.id IN('4','5')
                                        )
                                    ) AS l_tbl
                                LEFT JOIN(
                                    SELECT CAST(soLine.qty * uom.weight AS DECIMAL(25, 2)) AS weight, CAST(soLine.qty * soLine.unitPrice AS DECIMAL(25, 2)) AS amt, soLine.itemCode, so.docDate, so.customerId, so.salesAgent
                                    FROM ac_sales_invoices AS so
                                        INNER JOIN ac_sales_invoice_dtls AS soLine ON so.customerId = soLine.customerId AND so.id = soLine.invoiceId AND so.deleted = 0
                                        INNER JOIN ac_stock_item_uoms AS uom ON soLine.itemCode = uom.itemCode AND soLine.customerId = uom.customerId AND soLine.uom = uom.uom AND uom.deleted = 0
                                    WHERE YEAR(so.docDate) = '$year' AND MONTH(so.docDate) = '$month' UNION ALL
                                    SELECT CAST(soLine.qty * uom.weight * -1 AS DECIMAL(25, 2)) AS weight, CAST(soLine.qty * soLine.unitPrice * -1 AS DECIMAL(25, 2)) AS amt, soLine.itemCode, so.docDate, so.customerId, so.salesAgent
                                        FROM ac_sales_credit_notes AS so
                                        INNER JOIN ac_sales_credit_note_dtls AS soLine ON so.customerId = soLine.customerId AND so.id = soLine.cnId AND so.deleted = 0
                                        INNER JOIN ac_stock_item_uoms AS uom ON soLine.itemCode = uom.itemCode AND soLine.customerId = uom.customerId AND soLine.uom = uom.uom AND uom.deleted = 0
                                    WHERE YEAR(so.docDate) = '$year' AND MONTH(so.docDate) = '$month'
                                ) r_tbl ON l_tbl.salesAgent = r_tbl.salesAgent AND l_tbl.customerId = r_tbl.customerId AND l_tbl.itemCode = r_tbl.itemCode
                                GROUP BY l_tbl.name, l_tbl.managerName, l_tbl.id, l_tbl.rptCategory");

        return Excel::download(new RetailExport($yearMonth, $results), "DAILY ITEM-$yearMonth.xlsx");
    }


    public function downloadProductSales(Request $request)
    {
        $salesAreas = $request->salesAreas;
        $yearMonth = $request->month;

        $companies = implode(",", Auth::user()->companies);
        $companies = '2,5'; // for testing
        $salesAreasStr = implode(",", $salesAreas);
        $year = explode("-", $yearMonth)[0];
        $month = explode("-", $yearMonth)[1];

        $results = DB::select("SELECT IFNULL(l_tbl.salesArea,'UNKNOWN') as salesArea, l_tbl.name as salesAgent, l_tbl.productName, l_tbl.productId,  sum( r_tbl.qty) as qty
                                FROM (SELECT sp.salesArea, c.code as companyCode, ur.name as name, sa.salesAgent, rpt.itemCode as itemCode, rpt.productName as productName, product.id as productId, sa.customerId, sp.monthTarget
                                        FROM users AS ur
                                            INNER JOIN ms_sales_people AS sp on sp.userId = ur.id
                                            INNER JOIN ms_sales_person_mappings AS spm on sp.id = spm.salespersonId
                                            INNER JOIN ac_sales_agents AS sa on spm.acSalesAgentId = sa.id
                                            INNER JOIN ms_companies AS c on sa.customerId = c.extId
                                            INNER JOIN rpt_product_mappings AS rpt on sa.customerId= rpt.customerId
                                            INNER JOIN rpt_products AS product on rpt.productName = product.product
                                        WHERE ur.status <> '3'
                                            AND spm.companyId in ($companies)
                                            AND ur.username not in (SELECT username FROM rpt_excluded_users)
                                            AND EXISTS (SELECT 1 FROM users AS u INNER JOIN user_roles AS r on u.id = r.user_id INNER JOIN roles AS rp on r.role_id = rp.id WHERE u.username = ur.username AND r.id in ('4','5'))
                                    ) AS l_tbl
                                    LEFT JOIN (
                                        SELECT soLine.qty as qty, soLine.itemCode, so.docDate, so.customerId, so.salesAgent FROM ac_sales_invoices AS so
                                        INNER JOIN ac_sales_invoice_dtls AS soLine on so.customerId = soLine.customerId AND so.id = soLine.invoiceId AND so.deleted = 0 WHERE YEAR(so.docDate) = '$year' AND MONTH(so.docDate) = '$month' UNION ALL
                                        SELECT soLine.qty * -1 as qty, soLine.itemCode, so.docDate, so.customerId, so.salesAgent FROM ac_sales_credit_notes AS so
                                        INNER JOIN ac_sales_credit_note_dtls AS soLine on so.customerId = soLine.customerId AND so.id = soLine.cnId AND so.deleted = 0 WHERE YEAR(so.docDate) = '$year' AND MONTH(so.docDate) = '$month'
                                        ) AS r_tbl ON l_tbl.salesAgent = r_tbl.salesAgent AND l_tbl.customerId = r_tbl.customerId AND l_tbl.itemCode = r_tbl.itemCode
                                WHERE l_tbl.salesArea IN ($salesAreasStr)
                                GROUP BY l_tbl.salesArea, l_tbl.name, l_tbl.productId, l_tbl.productName");
        return Excel::download(new RetailExport($yearMonth, $results), "Product-Sales-Report-$yearMonth.xlsx");
    }

}
