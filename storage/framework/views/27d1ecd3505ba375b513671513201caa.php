
<?php echo $__env->make('dashboards.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container-fluid page-body-wrapper">
  <?php echo $__env->make('dashboards.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-sm-12">
          
          <div class="container-fluid">
              <h3 class="mb-4">Managers</h3>

              <table class="table table-striped table-bordered">
                  <thead>
                      <tr>
                          <th>#</th>
                          <th>Country</th>
                          <th>Full Name</th>
                          <th>Email</th>
                          <th>Phone</th>
                          <th>Password</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                          <tr>
                              <td><?php echo e($index + 1); ?></td>
                              <td><?php echo e($user->country->name ?? 'N/A'); ?></td>
                              <td><?php echo e($user->name); ?></td>
                              <td><?php echo e($user->email); ?></td>
                              <td><?php echo e($user->phone ?? '-'); ?></td>
                              <td>
                                 <?php
                                    try {
                                        echo $user->temp_pass ? decrypt($user->temp_pass) : 'Null';
                                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                                        echo 'Invalid/Corrupted';
                                    }
                                ?>
                              </td>
                              <td>
                                  <a href="<?php echo e(route('managers.edit', $user->id)); ?>" class="btn btn-sm btn-warning">Edit</a>
                                  
                                  <form method="POST" action="<?php echo e(route('manager.toggleStatus', $user->id)); ?>" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <button type="submit" class="btn btn-sm btn-<?php echo e($user->status == 1 ? 'danger' : 'success'); ?>">
                                      <?php echo e($user->status == 1 ? 'Deactivate' : 'Activate'); ?>

                                    </button>
                                  </form>
                              </td>
                          </tr>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                          <tr>
                              <td colspan="5" class="text-center">No managers found.</td>
                          </tr>
                      <?php endif; ?>
                  </tbody>
              </table>
          </div>

        </div> <!-- col-sm-12 -->
      </div> <!-- row -->
    </div> <!-- content-wrapper -->

    <?php echo $__env->make('dashboards.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </div> <!-- main-panel -->
</div> <!-- page-body-wrapper -->
</div> <!-- container-scroller -->
<?php /**PATH /home/malikautonet/public_html/resources/views/dashboards/users/managers.blade.php ENDPATH**/ ?>