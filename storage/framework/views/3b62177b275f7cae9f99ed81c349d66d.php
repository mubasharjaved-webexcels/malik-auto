<nav class="sidebar sidebar-offcanvas bg-white shadow-lg" id="sidebar">
  <ul class="nav">
    <li class="nav-item">
      <a class="nav-link border-bottom py-2" href="<?php echo e(url('dashboard')); ?>" style="border-radius:0;">
        <i class="mdi mdi-view-dashboard-outline me-2" style="font-size: 14px;"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link border-bottom py-2" href="<?php echo e(route('car-profiles.index')); ?>" style="border-radius:0;">
        <i class="mdi mdi-car me-2" style="font-size: 14px;"></i>
        <span class="menu-title">Car Profiles</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link border-bottom py-2" data-bs-toggle="collapse" href="#expensesSubmenu" role="button" aria-expanded="false" aria-controls="expensesSubmenu" style="border-radius:0;">
        <i class="mdi mdi-cash-multiple me-2" style="font-size: 14px;"></i>
        <span class="menu-title">Expenses</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="expensesSubmenu">
        <ul class="nav flex-column sub-menu py-0">
          <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('car-expenses.index')); ?>">
              <i class="mdi mdi-car menu-icon" style="font-size: 12px;"></i>
              Car Expenses
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('office-expenses.index')); ?>">
              <i class="mdi mdi-office-building menu-icon" style="font-size: 12px;"></i>
              Office Expenses
            </a>
          </li>
        </ul>
      </div>
    </li>
    <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager|salesperson')): ?>
    <li class="nav-item">
      <a class="nav-link border-bottom py-2" href="<?php echo e(route('users.managers')); ?>" style="border-radius:0;">
        <i class="mdi mdi-account-group me-2" style="font-size: 14px;"></i>
        <span class="menu-title">Managers Team</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link border-bottom py-2" href="<?php echo e(route('car_profiles.getPendingRequests')); ?>" style="border-radius:0;">
        <i class="mdi mdi-timer-sand me-2" style="font-size: 14px;"></i>
        <span class="menu-title">Pending Approvals</span>
        <?php if($pendingApprovalsCount > 0 ): ?>
            <span class="badge rounded-circle bg-danger" style="font-size: 10px; padding: 4px 6px; margin-left: 10px;">
                <?php echo e($pendingApprovalsCount); ?>

            </span>
        <?php endif; ?>
      </a>
    </li>
    <?php endif; ?>
    <li class="nav-item">
      <a class="nav-link border-bottom py-2" data-bs-toggle="collapse" href="#accountsSubmenu" role="button" aria-expanded="false" aria-controls="accountsSubmenu" style="border-radius:0;">
        <i class="mdi mdi-bank me-2" style="font-size: 14px;"></i>
        <span class="menu-title">Accounts</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="accountsSubmenu"> 
        <ul class="nav flex-column sub-menu py-0">
          <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'manager')): ?>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo e(route('accounts.journal-voucher')); ?>">
                <i class="mdi mdi-file-document menu-icon" style="font-size: 12px;"></i>
                Journal voucher
              </a>
            </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('accounts.index', ['type' => 'bank'])); ?>">
              <i class="mdi mdi-bank menu-icon" style="font-size: 12px;"></i>
              Bank Accounts
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('accounts.index', ['type' => 'cash'])); ?>">
              <i class="mdi mdi-cash menu-icon" style="font-size: 12px;"></i>
              Cash in Hand
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('accounts.profit_by_cars')); ?>">
              <i class="mdi mdi-car-key menu-icon" style="font-size: 12px;"></i>
              Profit by Cars
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('accounts.overall_profit_loss')); ?>">
              <i class="mdi mdi-chart-line menu-icon" style="font-size: 12px;"></i>
              Overall P/L
            </a>
          </li>
        </ul>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link border-bottom py-2" href="<?php echo e(route('currency.rates')); ?>" style="border-radius:0;">
        <i class="mdi mdi-currency-usd me-2" style="font-size: 14px;"></i>
        <span class="menu-title">Currency Exchange</span>
      </a>
    </li>
  </ul>
</nav>

<style>
.menu-arrow {
  margin-left: auto;
  transition: transform 0.3s ease;
}

.menu-arrow:before {
  content: "\F0142";
  font-family: "Material Design Icons";
  font-size: 14px;
}

.nav-link[aria-expanded="true"] .menu-arrow {
  transform: rotate(90deg);
}

.sub-menu {
  padding-left: 20px;
  background-color: #f8f9fa;
  padding-left: 15px !important;
}

.sub-menu .nav-link {
  padding: 10px 15px;
  font-size: 14px;
  color: #6c757d;
  border-bottom: 1px solid #e9ecef;
}

.sub-menu .nav-link:hover {
  background-color: #e9ecef;
  color: #495057;
}

.sub-menu .menu-icon {
  margin-right: 10px;
}
</style><?php /**PATH C:\wamp64\www\malik_auto\resources\views/dashboards/partials/sidebar.blade.php ENDPATH**/ ?>