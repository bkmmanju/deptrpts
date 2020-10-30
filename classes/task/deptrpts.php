<?php
namespace local_deptrpts\task;
/**
 * An example of a scheduled task.
 */
class deptrpts extends \core\task\scheduled_task {
 
    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('deptrpts', 'local_deptrpts');
    }
 
    /**
     * Execute the task.
     */
    public function execute() {
        // Apply fungus cream.
        // Apply chainsaw.
        // Apply olive oil.
    }
}