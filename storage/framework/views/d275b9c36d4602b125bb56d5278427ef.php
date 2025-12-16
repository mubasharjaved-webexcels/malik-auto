
<?php echo $__env->make('dashboards.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
    $labels = $monthlyData->pluck('month');
    $auctionPrices = $monthlyData->pluck('total_price');
    $expenses = $monthlyData->pluck('total_expenses');
?>




<div class="container-fluid page-body-wrapper">
  <?php echo $__env->make('dashboards.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="main-panel">
    <div class="content-wrapper px-3">
      <div class="row">
        <div class="col-sm-12">
          <div class="home-tab">
            <div class="tab-content-basic">
              <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row">
                  <div class="col-lg-8 flex-column pe-0">

                    <!-- Card 1 -->
                    <div class="container mb-4 px-0">
                      <div class="row g-3">
                        <?php $i = 1; ?>
                        <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-3 col-sm-6">
                              <div class="card h-100 shadow-sm border-0 rounded-3 d-flex flex-column justify-content-between text-center">
                              <a class="text-decoration-none" href="<?php echo e($country->car_profiles_count != 0 ? route('car-profiles.index', ['country_id' => $country->id]) : 'javascript:void(0)'); ?>">
                              <div class="card-body">
                                <h6 class="fw-bold text-dark mb-2"><?php echo e($country->name); ?></h6>
                                <p class="mb-1 small">Total Cars</p>
                                <h3 class="fw-bold text-primary mb-3"><?php echo e($country->car_profiles_count); ?></h3>
                                <img src="<?php echo e(asset("admin-assets/images/cars/c$i.png")); ?>" class="img-fluid mt-2" alt="Car <?php echo e($i); ?>">
                              </div>
                              </a>
                            </div>
                          </div>
                          <?php $i++ ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                          <?php $imageIndex = 5; ?>
                          <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusKey => $statusLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-3 col-sm-6">
                                <div class="card h-100 shadow-sm border-0 rounded-3 d-flex flex-column justify-content-between text-center">
                                    <a class="text-decoration-none"
                                        href="<?php echo e(($statusCounts[$statusKey] ?? 0) > 0 
                                            ? route('car-profiles.index', [
                                                'per_page'   => $per_page_value,
                                                'car_status' => $statusKey
                                              ]) 
                                            : 'javascript:void(0)'); ?>">
                                        <div class="card-body px-2">
                                            <h6 class="fw-semibold text-dark mb-2"><?php echo e($statusLabel); ?></h6>
                                            <p class="mb-1 small">Total Cars</p>
                                            <h3 class="fw-bold text-primary mb-3"><?php echo e($statusCounts[$statusKey] ?? 0); ?></h3>
                                            <img src="<?php echo e(asset("admin-assets/images/cars/c{$imageIndex}.png")); ?>?v=<?php echo e(time()); ?>" class="img-fluid" alt="Car Image">
                                        </div>
                                    </a>
                                </div>
                            </div>
                              <?php
                                  $imageIndex++;
                                  if ($imageIndex > 12) $imageIndex = 1;
                              ?>
                          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      </div>
                    </div>

                    <!-- Investment Overview -->
                    <?php
                        $totalAuctionPrice = $auctionPrices->sum();
                        $totalExpenses = $expenses->sum();
                    ?>
                    <div class="row flex-grow">
                      <div class="col-12 grid-margin stretch-card">
                        <div class="card card-rounded">
                          <div class="card-body">
                            <div class="d-sm-flex justify-content-between align-items-start">
                                <h4 class="card-title card-title-dash">Filter Data </h4>
                              </div>  
                              <form id="filterForm" method="GET" action="<?php echo e(route('admin.dashboard')); ?>">
                                <div class="row align-items-end mb-3">
                                  <div class="col-md-2">
                                    <label for="start_date" class="form-label fw-bold">From</label>
                                  </div>
                                  <div class="col-md-3">
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo e(request('start_date')); ?>">
                                  </div>
                                  <div class="col-md-2">
                                    <label for="end_date" class="form-label fw-bold">To</label>
                                  </div>
                                  <div class="col-md-3">
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo e(request('end_date')); ?>">
                                  </div>

                                  <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100 text-white mb-2">Filter</button>
                                  </div>
                                </div>
                              </form>
                              <div style="
                                  text-align: right;
                                ">
                                <h6 class="me-2 fw-bold"> Total Auction Price: $<?php echo e($totalAuctionPrice); ?></h6>
                                <h6 class="me-2 fw-bold">Total Expenses: $<?php echo e($totalExpenses); ?> </h6>
                              </div>

                              <div class="d-sm-flex justify-content-between align-items-start">
                                <h4 class="card-title card-title-dash">Total Car Investment</h4>
                              </div>    
                              <div class="d-sm-flex align-items-center mt-1 justify-content-between">
                                <div class="d-sm-flex align-items-center mt-4 justify-content-between">
                                  
                                  <h2 class="me-2 fw-bold">$<?php echo e(number_format($totalAuctionPrice + $totalExpenses, 2)); ?></h2>
                                  <h4 class="me-2">USD</h4>
                                  <!-- <h4 class="text-success">(+1.37%)</h4> -->
                                
                                </div>
                                <div class="me-3">
                                  <div id="marketingOverview-legend"></div>
                                </div>
                              </div>
                            <div class="chartjs-bar-wrapper mt-3">
                              <canvas id="marketingOverview"></canvas>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    
                    
                    
                    
                    <hr>
                    <div class="row">
                      <div class="col-sm-12 mb-4">
                        <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>">
                          <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                              <label for="start_date" class="form-label">Start Date</label>
                              <input type="date" id="start_date" name="start_date" value="<?php echo e(request('start_date')); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                              <label for="end_date" class="form-label">End Date</label>
                              <input type="date" id="end_date" name="end_date" value="<?php echo e(request('end_date')); ?>" class="form-control">
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                              <button type="submit" class="btn btn-primary text-white mb-1 mt-1">Filter</button>
                              <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-secondary text-white mb-1 mt-1">Reset</a>
                            </div>
                          </div>
                        </form>
                      </div>

                      <!-- Car Expenses Table -->
                      <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0">
                          <div class="card-header bg-light text-dark fw-bold" style="background-color: #d6d6f5;">
                            Total Car Expense
                          </div>
                          <div class="card-body p-0">
                            <?php if($carExpenses->isEmpty()): ?>
                              <div class="alert alert-warning text-center m-2">No car expenses found.</div>
                            <?php else: ?>
                              <table class="table table-bordered mb-0">
                                <thead>
                                  <tr>
                                    <th>Country Name</th>
                                    <th class="text-end">Amount</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php $__currentLoopData = $carExpenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                      <td><?php echo e($row->country_name); ?></td>
                                      <td class="text-end">$<?php echo e(number_format($row->total_usd, 2)); ?></td>
                                    </tr>
                                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                    <tr>
                                      <td class="fw-bold">Total</td>
                                      <td class="text-end">$<?php echo e(number_format($totalCarExpense, 2)); ?></td>
                                    </tr>
                              </table>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                      
                      <!-- Office Expenses Table -->
                      <div class="col-md-6 mb-4" style="padding-left:0;">
                        <div class="card shadow-sm border-0">
                          <div class="card-header bg-light text-dark fw-bold" style="background-color: #d6d6f5;">
                            Total Office Expense
                          </div>
                          <div class="card-body p-0">
                            <?php if($officeExpenses->isEmpty()): ?>
                              <div class="alert alert-warning text-center m-2">No office expenses found.</div>
                            <?php else: ?>
                              <table class="table table-bordered mb-0">
                                <thead>
                                  <tr>
                                    <th>Country Name</th>
                                    <th class="text-end">Amount</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php $__currentLoopData = $officeExpenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                      <td><?php echo e($row->country_name); ?></td>
                                      <td class="text-end">$<?php echo e(number_format($row->total_usd, 2)); ?></td>
                                    </tr>
                                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                    <tr>
                                      <td class="fw-bold">Total</td>
                                      <td class="text-end">$<?php echo e(number_format($totalOfficeExpense, 2)); ?></td>
                                    </tr>
                              </table>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="row">
                      <!-- Total Car Sold -->
                      <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0">
                          <div class="card-header bg-light text-dark fw-bold" style="background-color: #d6d6f5;">
                            Total Car Sold
                          </div>
                          <div class="card-body p-0">
                            <?php if($soldPrices->isEmpty()): ?>
                              <div class="alert alert-warning text-center m-2">No sold price found.</div>
                            <?php else: ?>
                              <table class="table table-bordered mb-0">
                                <thead>
                                  <tr>
                                    <th>Country</th>
                                    <th class="text-end">Amount</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php $__currentLoopData = $soldPrices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sold): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($sold->country_name); ?></td>
                                        <td class="text-end">
                                            <?php echo e($sold->total_sold_price > 0 ? '$' . number_format($sold->total_sold_price, 2) : '-'); ?>

                                        </td>
                                    </tr>
                                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                    <tr>
                                      <td class="fw-bold">Total</td>
                                      <td class="text-end">$<?php echo e(number_format($totalSoldPrice, 2)); ?></td>
                                    </tr>
                              </table>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>

                      <!-- Cash In Bank/Hand -->
                      <div class="col-md-6 mb-4" style="padding-left:0;">
                        <div class="card shadow-sm border-0">
                          <div class="card-header bg-light text-dark fw-bold" style="background-color: #d6d6f5;">
                            Cash In Bank/Hand
                          </div>
                          <div class="card-body p-0">
                            <table class="table table-bordered mb-0">
                              <thead>
                                <tr>
                                  <th>Country</th>
                                  <th class="text-end">In Bank</th>
                                  <th class="text-end">In Cash</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                  <?php
                                      $summary = $cashSummary->get(strtolower($country->name), collect());
                                      $bankTotal = optional($summary->firstWhere('type', 'bank'))->total_balance ?? 'N/A';
                                      $cashTotal = optional($summary->firstWhere('type', 'cash'))->total_balance ?? 'N/A';
                                  ?>
                                  <tr>
                                      <td><?php echo e($country->name); ?></td>
                                      <td class="text-end"><?php echo e(is_numeric($bankTotal) ? number_format($bankTotal, 2) : 'N/A'); ?></td>
                                      <td class="text-end"><?php echo e(is_numeric($cashTotal) ? number_format($cashTotal, 2) : 'N/A'); ?></td>
                                  </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                  </div>

                  <!-- Right Column -->
                  <div class="col-lg-4 d-flex flex-column">
                    <div class="card mb-2" style="width: 21.5rem;">
                      <img src="<?php echo e(asset('admin-assets/images/cars/c-right.png')); ?>" class="card-img-top" alt="...">
                    </div>
                    <!-- Last Login -->
                    <div class="row mt-3">
                      <div class="col-12 grid-margin">
                        <div class="card shadow rounded-lg">
                          <div class="card-body">
                            <h3 class="text-center border-b pb-3 text-gray-800 font-semibold text-lg">
                              Last Login
                            </h3>

                            <div class="d-flex justify-content-between mt-4 mb-3">
                              <h4 class="card-title-dash text-gray-700 font-medium"> Name</h4>
                              <h4 class="card-title-dash text-gray-700 font-medium"> Login at</h4>
                            </div>

                            <div class="d-flex justify-content-between">
                              <p class="text-gray-900 font-medium"><?php echo e(ucwords(auth()->user()->name)); ?></p>
                              <p class="text-gray-900 font-medium"><?php echo e(now()->format('d-m-Y')); ?></p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- Quick Links -->
                    <div class="row">
                      <div class="col-12 grid-margin">
                        <div class="card shadow rounded-lg">
                          <div class="card-body">
                            <h4 class="card-title text-center border-b pb-3 text-gray-800 font-semibold text-lg">
                              Quick Links
                            </h4>

                            <div class="d-flex justify-content-between mt-4 mb-3">
                              <a href="<?php echo e(route('car-expenses.index')); ?>" class="card-link text-primary hover:text-blue-700 transition duration-200 font-medium">
                                Car Expenses
                              </a>
                              <a href="<?php echo e(route('office-expenses.index')); ?>" class="card-link text-primary hover:text-blue-700 transition duration-200 font-medium">
                                Office Expenses
                              </a>
                            </div>

                            <div class="d-flex justify-content-between mb-3">
                              <a href="<?php echo e(route('accounts.index', ['type' => 'bank'])); ?>" class="card-link text-primary hover:text-blue-700 transition duration-200 font-medium">
                                Bank Accounts
                              </a>
                              <a href="<?php echo e(route('accounts.index', ['type' => 'cash'])); ?>" class="card-link text-primary hover:text-blue-700 transition duration-200 font-medium">
                                Cash Accounts
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                  </div>
                </div>
              </div> <!-- tab-pane -->
            </div> <!-- tab-content -->
          </div> <!-- home-tab -->
        </div> <!-- col-sm-12 -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->
    
    
    <script>
        const labels = <?php echo json_encode($labels, 15, 512) ?>;
        const auctionPrices = <?php echo json_encode($auctionPrices, 15, 512) ?> ;
        const expenses = <?php echo json_encode($expenses, 15, 512) ?>;
    </script>
    <script src="https://malikautonet.com/public/admin-assets/js/dashboard.js"></script>
    

    <?php echo $__env->make('dashboards.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
</div> <!-- container-scroller -->
<?php /**PATH C:\wamp64\www\malik_auto\resources\views/dashboards/admin.blade.php ENDPATH**/ ?>