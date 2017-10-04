<?php

use Illuminate\Database\Seeder;

class UsersLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users_logs')->insert(
            [
                'user_id' => 'auth0|5984bc8372b7861aa78ed934',
                'event_type' => 'UPDATE',
                'data_field' => 'First name',
                'old_value' => 'lolo',
                'new_value' => 'lolo123',
                'created_by' => 'Lolo test',
                'created_by_id' => 'auth0|5984bc8372b7861aa78ed934',
                'created_at' => date('Y/m/d H:i:s')
            ]
        );
        DB::table('users_logs')->insert(
            [
                'user_id' => 'auth0|5984bc8372b7861aa78ed934',
                'event_type' => 'UPDATE',
                'data_field' => 'First name',
                'old_value' => 'lolo',
                'new_value' => 'lolo345',
                'created_by' => 'Lolo test',
                'created_by_id' => 'auth0|5984bc8372b7861aa78ed934',
                'created_at' => date('Y/m/d H:i:s')
            ]
        );

        DB::table('users_logs')->insert(
            [
                'user_id' => 'auth0|5984bc8372b7861aa78ed934',
                'event_type' => 'UPDATE',
                'data_field' => 'First name',
                'old_value' => 'lolo',
                'new_value' => 'lolo',
                'created_by' => 'Lolo test',
                'created_by_id' => 'auth0|5984bc8372b7861aa78ed934',
                'created_at' => date('Y/m/d H:i:s')
            ]
        );

        DB::table('users_logs')->insert(
            [
                'user_id' => 'auth0|5984bc8372b7861aa78ed934',
                'event_type' => 'UPDATE',
                'data_field' => 'First name',
                'old_value' => 'lolo',
                'new_value' => 'lolo123',
                'created_by' => 'Lolo test',
                'created_by_id' => 'auth0|5984bc8372b7861aa78ed934',
                'created_at' => date('Y/m/d H:i:s')
            ]
        );
    }
}
