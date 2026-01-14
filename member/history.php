<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = (int) $_SESSION['customer_id'];

$q = mysqli_query($conn,"
    SELECT 
        p.payment_ID,
        p.payment_Date,
        p.amount,
        p.payment_method,
        p.payment_status,
        mp.plan_Name,
        mp.plan_Duration
    FROM payment p
    JOIN membershipplan mp ON p.plan_ID = mp.plan_ID
    WHERE p.customer_ID = $id
    ORDER BY p.payment_Date DESC
");
?>

<!-- Add CSS Styles -->
<style>
    .history-page {
        padding: 30px 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .page-header h2 {
        margin: 0;
        color: #023020 ;
        font-size: 28px;
    }
    
    .history-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .history-table th {
        background: linear-gradient(135deg, #30693b 0%, #30693b 100%);
        color: white;
        font-weight: 600;
        text-align: left;
        padding: 15px 20px;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .history-table td {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        color: #555;
    }
    
    .history-table tr:last-child td {
        border-bottom: none;
    }
    
    .history-table tr:hover {
        background-color: #f9f9f9;
    }
    
    .status {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status.paid {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status.pending {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .status.failed {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .btn-invoice {
        display: inline-block;
        padding: 8px 20px;
        background: linear-gradient(135deg, #30693b 0%, #30693b 100%);
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }
    
    .btn-invoice:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(106, 17, 203, 0.3);
        background: linear-gradient(135deg, #5a0db5 0%, #1c68e8 100%);
    }
    
    .payment-method {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .payment-icon {
        width: 30px;
        height: 20px;
        object-fit: contain;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    
    .empty-state i {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    .total-summary {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .summary-item {
        text-align: center;
    }
    
    .summary-label {
        display: block;
        color: #666;
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .summary-value {
        display: block;
        font-size: 24px;
        font-weight: 600;
        color: #333;
    }
    
    @media (max-width: 768px) {
        .history-table {
            display: block;
            overflow-x: auto;
        }
        
        .history-table th,
        .history-table td {
            padding: 12px 15px;
            font-size: 13px;
        }
        
        .btn-invoice {
            padding: 6px 15px;
            font-size: 12px;
        }
        
        .total-summary {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }
    }
</style>

<section class="history-page">
    <div class="page-header">
        <h2>Payment History</h2>
        <span class="summary-value">Total Payments: 
            <?php 
                $total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payment WHERE customer_ID = $id"));
                echo $total['count'];
            ?>
        </span>
    </div>

    <!-- Summary Section -->
    <?php
    $stats = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT 
            COUNT(*) as total_payments,
            SUM(amount) as total_amount,
            MAX(payment_Date) as last_payment
        FROM payment 
        WHERE customer_ID = $id
    "));
    ?>
    
    <div class="total-summary">
        <div class="summary-item">
            <span class="summary-label">Total Payments</span>
            <span class="summary-value"><?= $stats['total_payments'] ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Amount</span>
            <span class="summary-value">RM <?= number_format($stats['total_amount'] ?? 0, 2) ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Last Payment</span>
            <span class="summary-value">
                <?= $stats['last_payment'] ? date('M d, Y', strtotime($stats['last_payment'])) : 'Never' ?>
            </span>
        </div>
    </div>

    <!-- Payment History Table -->
    <div class="table-container">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Membership Plan</th>
                    <th>Duration</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($q) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($q)): ?>
                    <tr>
                        <td><?= date('M d, Y h:i A', strtotime($row['payment_Date'])) ?></td>
                        <td><strong><?= htmlspecialchars($row['plan_Name']) ?></strong></td>
                        <td><?= $row['plan_Duration'] ?> days</td>
                        <td><strong>RM <?= number_format($row['amount'], 2) ?></strong></td>
                        <td>
                            <div class="payment-method">
                                <?php 
                                $icon = '';
                                switch($row['payment_method']) {
                                    case 'CARD': $icon = 'visa.jpg'; break;
                                    case 'ONLINE': $icon = 'fpx.jpg'; break;
                                    case 'EWALLET': $icon = 'ewallet.jpg'; break;
                                }
                                if ($icon): ?>
                                    <img src="/pilates/assets/<?= $icon ?>" alt="<?= $row['payment_method'] ?>" class="payment-icon">
                                <?php endif; ?>
                                <span><?= $row['payment_method'] ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="status <?= strtolower($row['payment_status']) ?>">
                                <?= htmlspecialchars($row['payment_status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="invoice.php?payment_id=<?= $row['payment_ID'] ?>" class="btn-invoice">
                                View Invoice
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i>📄</i>
                                <h3>No Payment History</h3>
                                <p>You haven't made any payments yet.</p>
                                <a href="membership.php" class="btn-invoice" style="margin-top: 15px;">
                                    Browse Memberships
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Optional: Add pagination if you have many records -->
    <?php
    $total_records = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM payment WHERE customer_ID = $id"));
    if ($total_records['total'] > 10): ?>
    <div class="pagination" style="margin-top: 30px; text-align: center;">
        <a href="#" class="btn-invoice" style="margin: 0 5px;">« Previous</a>
        <a href="#" class="btn-invoice" style="margin: 0 5px; background: #ddd; color: #333;">1</a>
        <a href="#" class="btn-invoice" style="margin: 0 5px;">2</a>
        <a href="#" class="btn-invoice" style="margin: 0 5px;">3</a>
        <a href="#" class="btn-invoice" style="margin: 0 5px;">Next »</a>
    </div>
    <?php endif; ?>
</section>

<?php include("../includes/footer.php"); ?>