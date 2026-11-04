<?php

use App\Domain\Scoring\ScoreLineInput;
use App\Domain\Scoring\ScoringEngine;

it('sums points per unit times quantity', function () {
    $result = (new ScoringEngine)->calculate([
        new ScoreLineInput(scoreObjectId: 1, code: 'A', pointsPerUnit: 15, isPenalty: false, quantity: 3),
        new ScoreLineInput(scoreObjectId: 2, code: 'B', pointsPerUnit: 10, isPenalty: false, quantity: 2),
    ]);

    expect($result->subtotal)->toBe(65)
        ->and($result->penaltyTotal)->toBe(0)
        ->and($result->total)->toBe(65);
});

it('subtracts penalties from the subtotal without a maximum cap', function () {
    $result = (new ScoringEngine)->calculate([
        new ScoreLineInput(scoreObjectId: 1, code: 'A', pointsPerUnit: 1000, isPenalty: false, quantity: 1),
        new ScoreLineInput(scoreObjectId: 2, code: 'P', pointsPerUnit: -10, isPenalty: true, quantity: 1),
    ]);

    expect($result->subtotal)->toBe(1000)
        ->and($result->penaltyTotal)->toBe(10)
        ->and($result->total)->toBe(990);
});

it('scores achievement lines once when achieved with zero quantity', function () {
    $result = (new ScoringEngine)->calculate([
        new ScoreLineInput(scoreObjectId: 1, code: 'ACH', pointsPerUnit: 25, isPenalty: false, achieved: true),
    ]);

    expect($result->total)->toBe(25);
});

it('rejects quantities above the configured maximum', function () {
    (new ScoringEngine)->calculate([
        new ScoreLineInput(scoreObjectId: 1, code: 'A', pointsPerUnit: 10, isPenalty: false, quantity: 5, maxQuantity: 3),
    ]);
})->throws(InvalidArgumentException::class);

it('reports a zero score when nothing is achieved', function () {
    $result = (new ScoringEngine)->calculate([]);

    expect($result->total)->toBe(0);
});
