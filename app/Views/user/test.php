<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => false, 'pageTitle' => 'Online Test']) ?>

<?= $this->section('content') ?>
<!-- Timer -->
<div class="test-timer">
    <div class="time" id="timer">30:00</div>
    <div class="label">Time Remaining</div>
</div>

<div class="container" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Learner's License Test</h2>
        <span class="badge badge-primary" style="font-size: 14px;">
            Question <span id="currentQ">1</span> of <?= $totalQuestions ?? 25 ?>
        </span>
    </div>

    <form id="testForm" action="/test/submit" method="post">
        <!-- Question Cards -->
        <?php
        $questions = $questions ?? [
            [
                'id' => 1,
                'question' => 'What does a red octagonal sign indicate?',
                'image' => null,
                'options' => ['Yield', 'Stop', 'Speed Limit', 'No Entry'],
            ],
            [
                'id' => 2,
                'question' => 'When approaching a pedestrian crossing, you should:',
                'image' => null,
                'options' => ['Speed up', 'Slow down and be prepared to stop', 'Honk continuously', 'Ignore the crossing'],
            ],
            [
                'id' => 3,
                'question' => 'What is the meaning of this traffic sign?',
                'image' => '/assets/images/signs/no-parking.png',
                'options' => ['No Waiting', 'No Parking', 'No Stopping', 'No U-Turn'],
            ],
        ];
        foreach ($questions as $index => $q):
        ?>
            <div class="question-card <?= $index > 0 ? 'd-none' : '' ?>" id="question-<?= $index + 1 ?>">
                <span class="question-number">Question <?= $index + 1 ?></span>

                <?php if ($q['image']): ?>
                    <img src="<?= esc($q['image']) ?>" alt="Question Image" class="question-image mb-3" style="max-height: 200px;">
                <?php endif; ?>

                <p class="question-text"><?= esc($q['question']) ?></p>

                <ul class="options-list">
                    <?php foreach ($q['options'] as $optIndex => $option): ?>
                        <li class="option-item" data-question="<?= $q['id'] ?>" data-option="<?= $optIndex + 1 ?>">
                            <input type="radio" name="answer_<?= $q['id'] ?>" value="<?= $optIndex + 1 ?>">
                            <span class="option-radio"></span>
                            <span><?= esc($option) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <!-- Navigation -->
        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-outline-primary" id="prevBtn" disabled>
                <i class="bi bi-arrow-left"></i> Previous
            </button>
            <button type="button" class="btn btn-primary" id="nextBtn">
                Next <i class="bi bi-arrow-right"></i>
            </button>
            <button type="submit" class="btn btn-success d-none" id="submitBtn">
                <i class="bi bi-check-circle"></i> Submit Test
            </button>
        </div>
    </form>

    <!-- Question Navigator -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Question Navigator</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2" id="questionNav">
                <?php for ($i = 1; $i <= count($questions ?? range(1, 25)); $i++): ?>
                    <button type="button" class="btn btn-outline-secondary btn-sm <?= $i === 1 ? 'active' : '' ?>"
                        data-question="<?= $i ?>" style="width: 40px; height: 40px;">
                        <?= $i ?>
                    </button>
                <?php endfor; ?>
            </div>
            <div class="mt-3">
                <small class="me-3"><span class="badge bg-success">&nbsp;</span> Answered</small>
                <small class="me-3"><span class="badge bg-primary">&nbsp;</span> Current</small>
                <small><span class="badge bg-secondary">&nbsp;</span> Not Answered</small>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let currentQuestion = 1;
    const totalQuestions = <?= count($questions ?? range(1, 25)) ?>;
    const answers = {};

    // Timer
    let timeLeft = 30 * 60;
    const timerEl = document.getElementById('timer');
    setInterval(() => {
        if (timeLeft <= 0) {
            document.getElementById('testForm').submit();
            return;
        }
        timeLeft--;
        const mins = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const secs = (timeLeft % 60).toString().padStart(2, '0');
        timerEl.textContent = `${mins}:${secs}`;
        if (timeLeft < 300) timerEl.style.color = 'var(--danger-color)';
    }, 1000);

    // Navigation
    function showQuestion(num) {
        document.querySelectorAll('.question-card').forEach(q => q.classList.add('d-none'));
        document.getElementById(`question-${num}`).classList.remove('d-none');
        document.getElementById('currentQ').textContent = num;

        document.getElementById('prevBtn').disabled = num === 1;
        if (num === totalQuestions) {
            document.getElementById('nextBtn').classList.add('d-none');
            document.getElementById('submitBtn').classList.remove('d-none');
        } else {
            document.getElementById('nextBtn').classList.remove('d-none');
            document.getElementById('submitBtn').classList.add('d-none');
        }

        // Update navigator
        document.querySelectorAll('#questionNav button').forEach(b => b.classList.remove('active', 'btn-primary'));
        document.querySelector(`#questionNav button[data-question="${num}"]`).classList.add('active', 'btn-primary');
    }

    document.getElementById('prevBtn').addEventListener('click', () => showQuestion(--currentQuestion));
    document.getElementById('nextBtn').addEventListener('click', () => showQuestion(++currentQuestion));

    document.querySelectorAll('#questionNav button').forEach(btn => {
        btn.addEventListener('click', function() {
            currentQuestion = parseInt(this.dataset.question);
            showQuestion(currentQuestion);
        });
    });

    // Mark answered
    document.querySelectorAll('.option-item').forEach(item => {
        item.addEventListener('click', function() {
            const qNum = this.closest('.question-card').id.split('-')[1];
            document.querySelector(`#questionNav button[data-question="${qNum}"]`).classList.add('btn-success');
        });
    });
</script>
<?= $this->endSection() ?>