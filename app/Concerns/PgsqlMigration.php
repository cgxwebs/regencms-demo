<?php

namespace App\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

trait PgsqlMigration
{
    private $blueprint;
    private $table_name;
    private $jobs;
    private $is_unlogged = false;

    public function pgsql(string $table_name, ?Blueprint $blueprint = null)
    {
        $this->setTable($table_name);
        $this->setBlueprint($blueprint);
        $this->jobs = [];
        $this->db = DB::getFacadeRoot();
    }

    public function setTable(string $name)
    {
        $this->table_name = $name;
    }

    public function setBlueprint(?Blueprint $blueprint)
    {
        $this->blueprint = $blueprint;
    }

    public function createId()
    {
        return $this->blueprint->id()->generatedAs();
    }

    /**
     * Adds a unique slug in lowercase
     * @param string $name
     * @return void
     */
    public function createUniqueSlug(string $name)
    {
        $col = $this->blueprint->string($name)->unique();
        $this->addCheck($name."_islower", "$name = lower($name)");
        return $col;
    }

    public function addChecks(string $name, ...$checks)
    {
        $clist = implode(' AND ', $checks);
        $this->addCheck($name, $clist);
    }

    public function addCheck(string $name, string $check)
    {
        $stmts = [
            'ALTER TABLE', $this->table_name,
            'ADD CONSTRAINT', $this->table_name.'_'.$name,
            'CHECK (', $check, ');'
        ];
        $this->addJob(implode(' ', $stmts));
    }

    public function addConstraint()
    {

    }

    public function processJobs()
    {
        foreach ($this->jobs as $stmt) {
            $this->db->statement($stmt);
        }
        $this->jobs = [];
    }

    // Making tables unlogged won't allow foreign key constraints
    public function setTableUnloggedForTesting()
    {
        if ($this->isTesting()) {
            $this->addJob('ALTER TABLE ' . $this->table_name . ' SET UNLOGGED');
        }
    }

    private function isTesting()
    {
        return env('APP_ENV', 'local') == 'testing';
    }

    private function addJob(string $stmt)
    {
        $this->jobs[] = $stmt;
    }
}
