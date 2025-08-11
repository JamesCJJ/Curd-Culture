<div class="contactMessages index content">
    <h2>Contact Submissions</h2>
    <?= $this->Flash->render() ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contactMessages as $m): ?>
                <tr>
                    <td><?= h($m->name) ?></td>
                    <td><?= h($m->email) ?></td>
                    <td><?= h(mb_strimwidth((string)$m->message, 0, 80, '…')) ?></td>
                    <td><?= h($m->created) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<<') ?>
            <?= $this->Paginator->prev('<') ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next('>') ?>
            <?= $this->Paginator->last('>>') ?>
        </ul>
        <p><?= $this->Paginator->counter() ?></p>
    </div>
</div>
