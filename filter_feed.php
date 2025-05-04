<?php
/**
 * Article Filtering and Sorting Module
 * 
 * This file handles the filtering of articles based on user's institute selection
 * and sorting preference. It provides a function to filter and sort articles 
 * and returns JSON data to be rendered by the frontend.
 */

// Include database connection and helper functions
require_once 'db_connect.php';
require_once 'helpers/db_helpers.php';

// Function to get all available institutes for filtering
function getAllInstitutes($pdo)
{
    $query = "SELECT DISTINCT institute FROM articles WHERE status = 'approved'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $institutes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Always include "All" option
    array_unshift($institutes, 'All');
    return $institutes;
}

// Handle AJAX requests for article filtering and sorting
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ||
    (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json')
) {

    // Get JSON data for AJAX request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Get filtering and sorting parameters
    $institutes = isset($data['institutes']) ? $data['institutes'] : ['All'];
    $sortOption = isset($data['sort']) ? $data['sort'] : 'new';

    // Get filtered and sorted articles
    $articles = getFilteredArticles($pdo, $institutes, $sortOption);

    // Start a session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Add is_owner flag to each article
    foreach ($articles as &$article) {
        // Check if the current logged-in user is the article owner
        $article['is_owner'] = (isset($_SESSION['user_id']) && $article['user_id'] == $_SESSION['user_id']);
    }

    // Return JSON response for AJAX
    header('Content-Type: application/json');
    echo json_encode($articles);
    exit;
}
?>