
<?php $__env->startSection('content'); ?>
<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center" style="background: url('<?php echo e(asset('images/showroom-blur.jpg')); ?>') center/cover no-repeat;">
    <div class="row shadow-lg rounded-4 overflow-hidden" style="max-width: 900px; background-color: rgba(255, 255, 255, 0.9);">
        <!-- Left Side: Form -->
        <div class="col-md-6 p-5">
            <h3 class="fw-bold mb-3">Welcome Back</h3>
            <p class="text-muted small">Enter your login credentials, including your username and password, to securely access your account.</p>
            <br>
            
            <form method="POST" action="<?php echo e(route('login')); ?>">
                <?php echo csrf_field(); ?>
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required autofocus>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="At least 8 characters" required>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <div></div>
                    <a href="/login" class="text-decoration-none small">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Sign in</button>
            </form>
            
            
            
            
        </div>
        <!-- Right Side: Image -->
        <div class="col-md-6 d-none d-md-block p-0">
            <img src="<?php echo e(asset('images/yellow-car.jpg')); ?>" alt="Showroom" class="img-fluid h-100 w-100" style="object-fit: cover;">
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/malikautonet/public_html/resources/views/auth/login.blade.php ENDPATH**/ ?>