<?php //include 'layout/header.php'; ?>

<div class="container mt-4">
    <h2>Create New User</h2>
    
    <form action="/upMVC-DEV/usersorm/store" method="POST" class="mt-4">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Create User</button>
        <a href="/upMVC-DEV/usersorm" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'layout/footer.php'; ?>
