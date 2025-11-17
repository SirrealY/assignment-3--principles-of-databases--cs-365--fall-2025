<?php

require_once 'includes/helpers.php';

// Variables for search

$searchTerm = '';
$searchResults = [];
$searchPerformed = false;


$insertMessage = '';
$updateMessage = '';
$deleteMessage = '';

// -- Search Functionality --
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_submit'])) {
    $searchTerm = trim($_POST['search_term'] ?? '');

    // Even if empty, mark that a search was performed
    $searchPerformed = true;

    if($searchTerm !== '') {
        $searchResults = searchCredentials($searchTerm);
    } else {
        $searchResults = [];
    }
}

// -- Insert Functionality --
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insert_submit'])) {
    // Retrieve and trim form data
    $siteName = trim($_POST['site_name'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    // Validate required fields
    if ($siteName === '' || $url === '' || $email === '' || $username === '' || $password === '') {
        $insertMessage = "Please fill in all required fields.";
    } else {
        $ok = insertEntry($siteName, $url, $email, $username, $password, $comment);

        if ($ok) {
            $insertMessage = "Entry added successfully!";
            // Clear form fields after successful insertion
            $siteName = $url = $email = $username = $password = $comment = '';
        } else {
            $insertMessage = "Failed to add entry. Please try again.";
        }
    }
}
// -- Update Functionality --
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_submit'])) {
    $siteNamePattern = trim($_POST['update_site_name'] ?? '');
    $newUrl          = trim($_POST['update_url'] ?? '');

    if($siteNamePattern === '' || $newUrl === '') {
        $updateMessage = "Please fill in all of the required fields.";
    } else {
        $updated = updateEntry($siteNamePattern, $newUrl);

        if($updated) {
            $updateMessage = "Updated URL to {$newUrl} for all entries matching '{$siteNamePattern}'.";
        } else {
            $updateMessage = "Failed to update entries. Please try again.";
        }
    }
}

// -- Delete Functionality --
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_submit'])) {
    $deleteTerm = trim($_POST['delete_term'] ?? '');

    if($deleteTerm === '') {
        $deleteMessage = "Please enter a term to delete by";
    } else {
        $rowsDeleted = deleteEntry($deleteTerm);

        if($rowsDeleted > 0) {
            $deleteMessage = "Deleted {$rowsDeleted} entries matching '{$deleteTerm}'.";
        } else {
            $deleteMessage = "No entries found matching '{$deleteTerm}'. You're safe for now.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> Password Manager - Assignment 3</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1> Password Manager <br><small> Principles of Databases - Assignment 3</small></h1>
</header>

<main>
    <!-- Clear Results Button -->
    <section>
        <form method="get">
            <button type="submit">Clear Results</button>
        </form>
    </section>

    <!-- Search Form -->
    <section>
        <h2>Search Entries</h2>
        <p> Search by username, email, site name, URL, site username, or comment. </p>

        <form method="post">
            <label for="search_term">Search Term:</label>
            <input
                type="text"
                id="search_term"
                name="search_term"
                value="<?php echo htmlspecialchars($searchTerm); ?>"
            >
            <button type="submit" name="search_submit">Search</button>
        </form>

        <?php if ($searchPerformed): ?>
            <h3> Search Results</h3>
            <table border="1" cellpadding="5" cellspacing="8">
                <thead>
                    <tr>
                        <th>Credential ID</th>
                        <th>User Username</th>
                        <th>Email</th>
                        <th>Site Name</th>
                        <th>Site Username</th>
                        <th>URL</th>
                        <th>Comment</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($searchResults) > 0): ?>
                    <?php foreach ($searchResults as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['credential_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['site_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['site_username']); ?></td>
                            <td><?php echo htmlspecialchars($row['url']); ?></td>
                            <td><?php echo htmlspecialchars($row['comment']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            No results found
                            <?php
                            if ($searchTerm !== '') {
                                echo " for <strong>" . htmlspecialchars($searchTerm) . "</strong>";
                            }
                            ?>.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <!-- Insert New Entry Form -->
    <section>
        <h2> Add New Entry</h2>
        <p> Add a new password entry to the database. </p>

        <?php if ($insertMessage !== '') : ?>
            <p class="highlight"><?php echo htmlspecialchars($insertMessage); ?></p>
        <?php endif; ?>

        <form method="post">
            <div>
                <label for="site_name">Site / App Name:</label>
                <input type="text" id="site_name" name="site_name" required>
            </div>

            <div>
                <label for="url">URL:</label>
                <input type="url" id="url" name="url" required>
            </div>

            <div>
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div>
                <label for="username">Account Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div>
                <label for="password">Account Password:</label>
                <input type="text" id="password" name="password" required>
            </div>

            <div>
                <label for="comment">Comment:</label>
                <textarea id="comment" name="comment"></textarea>
            </div>
            <button type="submit" name="insert_submit">Add Entry</button>
        </form>
    </section>

    <!-- Update Entry Form -->
    <section>
        <h2> Update Entry by Site / App Name</h2>
        <p> Update the URL for any website whos name matches the provivded pattern.</p>

        <?php if ($updateMessage !== ''): ?>
            <p class='highlight'><?php echo htmlspecialchars($updateMessage); ?></p>
        <?php endif; ?>

        <form method="post">
            <div>
                <label for="update_site_name">Site / App Name (pattern):</label>
                <input
                    type="text"
                    id="update_site_name"
                    name="update_site_name"
                    required
                >
            </div>

            <div>
                <label for="update_url">New URL:</label>
                <input
                    type="url"
                    id="update_url"
                    name="update_url"
                    required
                >
            </div>

            <button type="submit" name="update_submit">Update Entry</button>
        </form>
    </section>

    <!-- Delete Entry Form -->
    <section>
        <h2> Delete Entries</h2>
        <p> Delete entries matching the provided username, email, site name, URL, site username, or comment.</p>

        <?php if ($deleteMessage !== ''): ?>
            <p class='highlight'><?php echo htmlspecialchars($deleteMessage); ?></p>
        <?php endif; ?>

        <form method="post">
            <div>
                <label for="delete_term">Delete Term (pattern):</label>
                <input
                    type="text"
                    id="delete_term"
                    name="delete_term"
                    required
                >
            </div>

            <button type="submit" name="delete_submit">Delete Entries</button>
        </form>
    </section>
</main>
</body>
</html>
