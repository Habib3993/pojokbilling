<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct()
    {
        // Setiap fungsi akan dicek izinnya sebelum dijalankan
        $this->middleware('can:view transactions')->only('index');
        $this->middleware('can:create transactions')->only(['create', 'store']);
        $this->middleware('can:edit transactions')->only(['edit', 'update']);
        $this->middleware('can:delete transactions')->only('destroy');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $transactionsQuery = Transaction::query()
            ->when($search, function ($query, $search) {
                return $query->where('note', 'like', "%{$search}%")
                             ->orWhere('status', 'like', "%{$search}%");
            })
            ->orderBy('date', 'asc') // Urutkan berdasarkan tanggal
            ->orderBy('id', 'asc');

        $transactions = $transactionsQuery->get();

        // Hitung saldo berjalan
        $runningBalance = 0;
        $transactionsWithBalance = $transactions->map(function ($transaction) use (&$runningBalance) {
            $runningBalance += $transaction->debit - $transaction->kredit;
            $transaction->balance = $runningBalance;
            return $transaction;
        });
        $transactionsForView = $transactionsWithBalance->reverse();
        return view('transactions.index', [
            'transactions' => $transactionsForView, 
            'currentBalance' => $runningBalance,
        ]);
    }

    public function create()
    {
        return view('transactions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'status' => 'required|string',
            'note' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
            'optional_note' => 'nullable|string',
        ]);

        $data = [
            'date' => $validated['date'],
            'status' => $validated['status'],
            'note' => $validated['note'],
            'optional_note' => $validated['optional_note'],
            'debit' => 0,
            'kredit' => 0,
        ];

        if ($validated['status'] === 'MODAL') {
            $data['debit'] = $validated['amount'];
        } else {
            $data['kredit'] = $validated['amount'];
        }

        Transaction::create($data);

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(Transaction $transaction)
    {
        return view('transactions.edit', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        // Logika update mirip dengan store
        $validated = $request->validate([
            'date' => 'required|date',
            'status' => 'required|string',
            'note' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
            'optional_note' => 'nullable|string',
        ]);

        $data = [
            'date' => $validated['date'],
            'status' => $validated['status'],
            'note' => $validated['note'],
            'optional_note' => $validated['optional_note'],
            'debit' => 0,
            'kredit' => 0,
        ];

        if ($validated['status'] === 'MODAL') {
            $data['debit'] = $validated['amount'];
        } else {
            $data['kredit'] = $validated['amount'];
        }

        $transaction->update($data);

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus.');
    }
}
