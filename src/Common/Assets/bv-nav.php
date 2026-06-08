<?php /** @var \App\Common\Bmvc\BaseView $this */ ?>
<nav class="bv-nav">
    <div class="bv-nav-inner">

        <!-- Brand -->
        <a href="<?php echo $base; ?>" class="bv-brand">⚡ <span>up</span>MVC</a>

        <!-- Primary links -->
        <ul class="bv-links">

            <?php if ($role === 'platform_admin'): ?>
            <li><a href="<?php echo $base; ?>/platform-admin" class="bv-hi <?php echo $this->isActive($base . '/platform-admin'); ?>">🛡 Platform Admin</a></li>
            <?php endif; ?>

            <?php if (in_array($role, ['tenant_owner','tenant_user'], true)): ?>
            <li><a href="<?php echo $base; ?>/app" class="bv-hi <?php echo $this->isActive($base . '/app'); ?>">🏢 My App</a></li>
            <?php endif; ?>

            <li class="bv-drop">
                <a href="<?php echo $base; ?>" class="<?php echo $this->dropActive([$base . '/admin', $base . '/test/modern', $base . '/dashboardexample']); ?>">Home ▾</a>
                <ul>
                    <li><a href="<?php echo $base; ?>/admin" class="<?php echo $this->isActive($base . '/admin'); ?>">Admin</a></li>
                    <li><a href="<?php echo $base; ?>/test/modern" class="<?php echo $this->isActive($base . '/test/modern'); ?>">Modern</a></li>
                    <li><a href="<?php echo $base; ?>/dashboardexample" class="<?php echo $this->isActive($base . '/dashboardexample'); ?>">Dashboard Example</a></li>
                </ul>
            </li>

            <li class="bv-drop">
                <a href="<?php echo $base; ?>/test" class="<?php echo $this->dropActive([$base . '/test', $base . '/test/subpage', $base . '/moda', $base . '/moda/subpage', $base . '/suba', $base . '/suba/subpage']); ?>">Routing ▾</a>
                <ul>
                    <li><a href="<?php echo $base; ?>/test" class="<?php echo $this->isActive($base . '/test'); ?>">Test</a></li>
                    <li><a href="<?php echo $base; ?>/test/subpage" class="<?php echo $this->isActive($base . '/test/subpage'); ?>">Subpage</a></li>
                    <li><a href="<?php echo $base; ?>/test/item/42">{id:int}</a></li>
                    <li><a href="<?php echo $base; ?>/test/article/my-slug">{slug}</a></li>
                    <li><a href="<?php echo $base; ?>/test/pair/hello/world">Two params</a></li>
                    <li><a href="<?php echo $base; ?>/moda" class="<?php echo $this->isActive($base . '/moda'); ?>">Moda</a></li>
                    <li><a href="<?php echo $base; ?>/moda/subpage" class="<?php echo $this->isActive($base . '/moda/subpage'); ?>">Moda Subpage</a></li>
                    <li><a href="<?php echo $base; ?>/suba" class="<?php echo $this->isActive($base . '/suba'); ?>">Suba</a></li>
                    <li><a href="<?php echo $base; ?>/suba/subpage" class="<?php echo $this->isActive($base . '/suba/subpage'); ?>">Suba Subpage</a></li>
                </ul>
            </li>

            <li class="bv-drop">
                <a href="#" class="<?php echo $this->dropActive([$base . '/users', $base . '/new', $base . '/reactcrud', $base . '/usersorm']); ?>">CRUD ▾</a>
                <ul>
                    <li><a href="<?php echo $base; ?>/users" class="<?php echo $this->isActive($base . '/users'); ?>">Users CRUD</a></li>
                    <li><a href="<?php echo $base; ?>/new" class="<?php echo $this->isActive($base . '/new'); ?>">Users CRUD PHPistols</a></li>
                    <li><a href="<?php echo $base; ?>/reactcrud" class="<?php echo $this->isActive($base . '/reactcrud'); ?>">Users CRUD React</a></li>
                    <li><a href="<?php echo $base; ?>/usersorm" class="<?php echo $this->isActive($base . '/usersorm'); ?>">Users CRUD ORM</a></li>
                </ul>
            </li>

            <li class="bv-drop">
                <a href="<?php echo $base; ?>/react" class="<?php echo $this->dropActive([$base . '/react', $base . '/reactb', $base . '/reactnb', $base . '/reacthmr']); ?>">JS ▾</a>
                <ul>
                    <li><a href="<?php echo $base; ?>/react" class="<?php echo $this->isActive($base . '/react'); ?>">React</a></li>
                    <li><a href="<?php echo $base; ?>/reactb" class="<?php echo $this->isActive($base . '/reactb'); ?>">ReactB</a></li>
                    <li><a href="<?php echo $base; ?>/reactnb" class="<?php echo $this->isActive($base . '/reactnb'); ?>">NoBuild</a></li>
                    <li><a href="<?php echo $base; ?>/reacthmr" class="<?php echo $this->isActive($base . '/reacthmr'); ?>">HMR</a></li>
                </ul>
            </li>

            <li><a href="<?php echo $base; ?>/apiInfo" class="<?php echo $this->isActive($base . '/apiInfo'); ?>">API Info</a></li>

            <li><a href="https://github.com/upMVC/upMVC/wiki/How%E2%80%90to-Page" target="_blank">Wiki ↗</a></li>

        </ul>

        <!-- Right side -->
        <div class="bv-right">
            <?php if ($logged): ?>
                <?php if ($roleBadge): ?>
                <span class="bv-role-badge"
                      style="background:<?php echo $roleBadge['bg']; ?>;color:<?php echo $roleBadge['fg']; ?>;">
                    <?php echo $roleBadge['label']; ?>
                </span>
                <?php endif; ?>
                <span class="bv-uname"><?php echo $uname; ?></span>
                <a href="<?php echo $base; ?>/logout" class="bv-btn">Sign out</a>
            <?php else: ?>
                <a href="<?php echo $base; ?>/auth" class="bv-btn bv-btn-primary">Sign in</a>
            <?php endif; ?>
        </div>

    </div>
</nav>
