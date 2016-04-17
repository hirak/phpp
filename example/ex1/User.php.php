<?= '<' . '?php' ?> 
<?php
$props = [
    'id' => 'int',
    'name' => 'string',
    'gender' => 'string',
    'birthday' => 'DateTime',
    'description' => 'string',
]
?>
class User
{
<?php foreach ($props as $p => $type): ?>
    protected $<?= $p ?>;

    public function get<?= ucfirst($p) ?>(): <?= $type ?> 
    {
        return $this-><?= $p ?>;
    }

    public function set<?= ucfirst($p) ?>(<?= $type ?> $value)
    {
        $this-><?= $p ?> = $value;
    }

<?php endforeach ?>
}
