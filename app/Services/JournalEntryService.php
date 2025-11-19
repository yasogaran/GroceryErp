<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JournalEntryService
{
    /**
     * Create a new journal entry with lines.
     *
     * @param array $data Entry data including lines
     * @return JournalEntry
     * @throws \Exception
     */
    public function createEntry(array $data): JournalEntry
    {
        DB::beginTransaction();

        try {
            // Generate entry number if not provided
            if (!isset($data['entry_number'])) {
                $data['entry_number'] = JournalEntry::generateEntryNumber();
            }

            // Set defaults
            // Use provided created_by, fallback to Auth::id(), then to system user (1)
            $data['created_by'] = $data['created_by'] ?? Auth::id() ?? 1;
            $data['status'] = $data['status'] ?? 'draft';
            $data['entry_type'] = $data['entry_type'] ?? 'manual';

            // Calculate totals from lines
            $totals = $this->calculateTotals($data['lines'] ?? []);
            $data['total_debit'] = $totals['debit'];
            $data['total_credit'] = $totals['credit'];

            // Create the journal entry
            $entry = JournalEntry::create($data);

            // Create lines
            if (isset($data['lines']) && is_array($data['lines'])) {
                foreach ($data['lines'] as $line) {
                    $entry->lines()->create([
                        'account_id' => $line['account_id'],
                        'description' => $line['description'] ?? null,
                        'debit' => $line['debit'] ?? 0,
                        'credit' => $line['credit'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            return $entry->fresh(['lines.account']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing journal entry (draft only).
     *
     * @param JournalEntry $entry
     * @param array $data
     * @return JournalEntry
     * @throws \Exception
     */
    public function updateEntry(JournalEntry $entry, array $data): JournalEntry
    {
        if (!$entry->canBeEdited()) {
            throw new \Exception('Only draft entries can be edited.');
        }

        DB::beginTransaction();

        try {
            // Calculate totals from lines
            if (isset($data['lines'])) {
                $totals = $this->calculateTotals($data['lines']);
                $data['total_debit'] = $totals['debit'];
                $data['total_credit'] = $totals['credit'];
            }

            // Update entry
            $entry->update($data);

            // Update lines if provided
            if (isset($data['lines']) && is_array($data['lines'])) {
                // Delete existing lines
                $entry->lines()->delete();

                // Create new lines
                foreach ($data['lines'] as $line) {
                    $entry->lines()->create([
                        'account_id' => $line['account_id'],
                        'description' => $line['description'] ?? null,
                        'debit' => $line['debit'] ?? 0,
                        'credit' => $line['credit'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            return $entry->fresh(['lines.account']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Post a journal entry (make it effective).
     *
     * @param JournalEntry $entry
     * @return JournalEntry
     * @throws \Exception
     */
    public function postEntry(JournalEntry $entry): JournalEntry
    {
        if (!$entry->canBePosted()) {
            throw new \Exception('Entry cannot be posted. Must be draft, balanced, and have at least 2 lines.');
        }

        DB::beginTransaction();

        try {
            // Update account balances
            foreach ($entry->lines as $line) {
                $this->updateAccountBalance($line->account, $line->debit, $line->credit);
            }

            // Update entry status
            $entry->update([
                'status' => 'posted',
                'posted_by' => Auth::id() ?? $entry->created_by ?? 1,
                'posted_at' => now(),
            ]);

            DB::commit();

            return $entry->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse a posted journal entry.
     *
     * @param JournalEntry $entry
     * @param string $reason
     * @return JournalEntry The reversal entry
     * @throws \Exception
     */
    public function reverseEntry(JournalEntry $entry, string $reason): JournalEntry
    {
        if (!$entry->canBeReversed()) {
            throw new \Exception('Only posted entries can be reversed.');
        }

        DB::beginTransaction();

        try {
            // Create reversal entry with opposite debits/credits
            $reversalData = [
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => now()->toDateString(),
                'description' => 'Reversal of ' . $entry->entry_number . ' - ' . $reason,
                'entry_type' => $entry->entry_type,
                'status' => 'draft',
                'reference_type' => get_class($entry),
                'reference_id' => $entry->id,
            ];

            // Reverse the lines (swap debit and credit)
            $reversalLines = [];
            foreach ($entry->lines as $line) {
                $reversalLines[] = [
                    'account_id' => $line->account_id,
                    'description' => $line->description,
                    'debit' => $line->credit, // Swap
                    'credit' => $line->debit, // Swap
                ];
            }

            $reversalData['lines'] = $reversalLines;

            // Create and post the reversal entry
            $reversalEntry = $this->createEntry($reversalData);
            $this->postEntry($reversalEntry);

            // Mark original entry as reversed
            $entry->update([
                'status' => 'reversed',
                'reversed_by' => Auth::id(),
                'reversed_at' => now(),
                'reversal_reason' => $reason,
            ]);

            DB::commit();

            return $reversalEntry;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a journal entry (draft only).
     *
     * @param JournalEntry $entry
     * @return bool
     * @throws \Exception
     */
    public function deleteEntry(JournalEntry $entry): bool
    {
        if (!$entry->canBeDeleted()) {
            throw new \Exception('Only draft entries can be deleted.');
        }

        DB::beginTransaction();

        try {
            $entry->lines()->delete();
            $entry->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate totals from lines array.
     *
     * @param array $lines
     * @return array ['debit' => total, 'credit' => total]
     */
    public function calculateTotals(array $lines): array
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as $line) {
            $totalDebit = bcadd($totalDebit, $line['debit'] ?? 0, 2);
            $totalCredit = bcadd($totalCredit, $line['credit'] ?? 0, 2);
        }

        return [
            'debit' => $totalDebit,
            'credit' => $totalCredit,
        ];
    }

    /**
     * Validate that lines are balanced.
     *
     * @param array $lines
     * @return bool
     */
    public function validateBalance(array $lines): bool
    {
        $totals = $this->calculateTotals($lines);
        return bccomp($totals['debit'], $totals['credit'], 2) === 0;
    }

    /**
     * Update account balance based on transaction.
     *
     * @param Account $account
     * @param string $debit
     * @param string $credit
     * @return void
     */
    private function updateAccountBalance(Account $account, string $debit, string $credit): void
    {
        // Calculate net change (debit increases, credit decreases for assets/expenses)
        // For liabilities/equity/income, credit increases, debit decreases
        $netChange = bcsub($debit, $credit, 2);

        // Apply change based on account type
        switch ($account->account_type) {
            case 'asset':
            case 'expense':
                // Debit increases, credit decreases
                $account->balance = bcadd($account->balance, $netChange, 2);
                break;

            case 'liability':
            case 'equity':
            case 'income':
                // Credit increases, debit decreases
                $account->balance = bcsub($account->balance, $netChange, 2);
                break;
        }

        $account->save();
    }

    /**
     * Create a simple two-line journal entry.
     *
     * @param array $data
     * @return JournalEntry
     * @throws \Exception
     */
    public function createSimpleEntry(
        string $entryDate,
        int $debitAccountId,
        int $creditAccountId,
        string $amount,
        string $description,
        string $entryType = 'manual',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $createdBy = null
    ): JournalEntry {
        $data = [
            'entry_date' => $entryDate,
            'description' => $description,
            'entry_type' => $entryType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'lines' => [
                [
                    'account_id' => $debitAccountId,
                    'description' => $description,
                    'debit' => $amount,
                    'credit' => 0,
                ],
                [
                    'account_id' => $creditAccountId,
                    'description' => $description,
                    'debit' => 0,
                    'credit' => $amount,
                ],
            ],
        ];

        if ($createdBy !== null) {
            $data['created_by'] = $createdBy;
        }

        return $this->createEntry($data);
    }
}
