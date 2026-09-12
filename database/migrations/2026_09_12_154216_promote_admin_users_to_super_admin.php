<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Designate the first Super Admin account(s).
     *
     * The general `admin` role remains the general admin: it keeps the general
     * dashboard and never reaches the private Super Admin space. Only the email
     * addresses explicitly listed in `config/private_spaces.php` are promoted,
     * so no administrator is ever promoted automatically.
     */
    public function up(): void
    {
        $emails = $this->designatedEmails();

        if ($emails === []) {
            return;
        }

        DB::table('users')
            ->whereIn('email', $emails)
            ->where('role', 'admin')
            ->update(['role' => 'super_admin']);
    }

    /**
     * Demote only the explicitly designated accounts: general administrators
     * are never touched by a rollback.
     */
    public function down(): void
    {
        $emails = $this->designatedEmails();

        if ($emails === []) {
            return;
        }

        DB::table('users')
            ->whereIn('email', $emails)
            ->where('role', 'super_admin')
            ->update(['role' => 'admin']);
    }

    /**
     * @return list<string>
     */
    private function designatedEmails(): array
    {
        return array_values(array_filter(
            (array) config('private_spaces.super_admin_emails', []),
            fn ($email) => is_string($email) && trim($email) !== '',
        ));
    }
};
