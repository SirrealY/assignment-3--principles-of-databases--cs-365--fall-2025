<?php

require_once 'includes/helpers.php';

// Variables for search

$searchTerm = '';
$searchResults = [];
$searchPerformed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search-submit'])) {
    $searchterm = trim($_POST['search-term'] ?? '');

    // Even if empty, mark that a search was performed
    $searchPerformed = true;

    if($searchTerm !== '') {
        $searchResults = searchCredentials($searchTerm);
    } else {
        $searchResults = [];
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
            <label for="search-term">Search Term:</label>
            <input
                type="text"
                id="search_term"
                name="search_term"
                value="<?php echo htmlspeacialchars($searchTerm); ?>"
            >
            <button type="submit" name="search-submit">Search</button>
        </form>

        <?php if ($searchPerformed): ?>
            <h3> Search Results
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
                                echo "for <strong>" . htmlspecialchars($searchTerm) . "</strong>";
                            }
                            ?>.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
