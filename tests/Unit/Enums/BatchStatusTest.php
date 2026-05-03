<?php

use App\Enums\BatchStatus;

it('has correct values for all statuses', function () {
    expect(BatchStatus::DRAFT->value)->toBe('draft')
        ->and(BatchStatus::SUBMITTED->value)->toBe('submitted')
        ->and(BatchStatus::VERIFIED->value)->toBe('verified')
        ->and(BatchStatus::APPROVED->value)->toBe('approved')
        ->and(BatchStatus::REJECTED->value)->toBe('rejected')
        ->and(BatchStatus::REVISION_REQUESTED->value)->toBe('revision_requested')
        ->and(BatchStatus::REVISED->value)->toBe('revised');
});

it('has correct labels', function () {
    expect(BatchStatus::DRAFT->label())->toBe('Draft')
        ->and(BatchStatus::SUBMITTED->label())->toBe('Diajukan')
        ->and(BatchStatus::APPROVED->label())->toBe('Disetujui')
        ->and(BatchStatus::REJECTED->label())->toBe('Ditolak');
});

it('draft and revised can be edited', function () {
    expect(BatchStatus::DRAFT->canEdit())->toBeTrue()
        ->and(BatchStatus::REVISED->canEdit())->toBeTrue()
        ->and(BatchStatus::SUBMITTED->canEdit())->toBeFalse()
        ->and(BatchStatus::APPROVED->canEdit())->toBeFalse();
});

it('only submitted can be verified', function () {
    expect(BatchStatus::SUBMITTED->canVerify())->toBeTrue()
        ->and(BatchStatus::DRAFT->canVerify())->toBeFalse()
        ->and(BatchStatus::APPROVED->canVerify())->toBeFalse();
});

it('only verified can be approved', function () {
    expect(BatchStatus::VERIFIED->canApprove())->toBeTrue()
        ->and(BatchStatus::SUBMITTED->canApprove())->toBeFalse()
        ->and(BatchStatus::DRAFT->canApprove())->toBeFalse();
});

it('only approved can request revision', function () {
    expect(BatchStatus::APPROVED->canRequestRevision())->toBeTrue()
        ->and(BatchStatus::SUBMITTED->canRequestRevision())->toBeFalse()
        ->and(BatchStatus::DRAFT->canRequestRevision())->toBeFalse();
});

it('options returns all statuses as key value', function () {
    $options = BatchStatus::options();
    expect($options)->toHaveCount(7)
        ->and($options['draft'])->toBe('Draft')
        ->and($options['approved'])->toBe('Disetujui');
});
