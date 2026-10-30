<?php

it('rejects unauthenticated requests to protected routes', function (string $method, string $uri) {
    $this->json($method, $uri)->assertUnauthorized();
})->with([
    'me' => ['GET', '/api/v1/me'],
    'logout' => ['POST', '/api/v1/auth/logout'],
    'list categories' => ['GET', '/api/v1/categories'],
    'create category' => ['POST', '/api/v1/categories'],
    'update category' => ['PUT', '/api/v1/categories/1'],
    'delete category' => ['DELETE', '/api/v1/categories/1'],
    'list budgets' => ['GET', '/api/v1/budgets'],
    'create budget' => ['POST', '/api/v1/budgets'],
    'show budget' => ['GET', '/api/v1/budgets/1'],
    'update budget' => ['PUT', '/api/v1/budgets/1'],
    'delete budget' => ['DELETE', '/api/v1/budgets/1'],
    'budget categories' => ['GET', '/api/v1/budgets/1/categories'],
    'budget members' => ['GET', '/api/v1/budgets/1/members'],
    'remove budget member' => ['DELETE', '/api/v1/budgets/1/members/1'],
]);
