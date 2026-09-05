<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';

// Render public classroom; progress saving requires login.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE-edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fante Classroom | Fantepedia</title>
  <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
  <?php include 'partials/header.php'; ?>
<section class="fc-hero">
      
  <div class="fc-wrap">
    <div class="fc-hero">
      <h1>Fante Classroom</h1>
      <p>Choose your Fante learner level, load the session quiz, answer questions, and get instant feedback.</p>
    </div>

    <div class="fc-grid">
      <div class="fc-card">
        <div class="fc-card-head">
          <h2>Quiz Setup</h2>
        </div>
        <div class="fc-form">
          <div class="row">
            <label>Quiz Category</label>
            <select id="fc_category">
              <option value="Beginner Learner">Beginner Learner</option>
              <option value="Intermediate Learner">Intermediate Learner</option>
              <option value="Advanced Learner">Advanced Learner</option>
            </select>
          </div>

          <div class="row">
            <label>Question Type</label>
            <select id="fc_kind">
              <option value="mcq">Multiple Choice Question</option>
              <option value="essay">Subjective Question</option>
            </select>
          </div>

          <div class="row">
            <label>Session Title</label>
            <select id="fc_session_title_type">
              <option value="">Select Session Type</option>
              <option value="mcq">MCQ Session</option>
              <option value="essay">Essay Session</option>
            </select>
          </div>

          <div class="fc-cta-row">

            <button class="fc-btn fc-btn-primary" type="button" id="fc_start"><i class="fa-solid fa-play"></i> Start</button>
          </div>

<div style="margin-top:10px" class="fc-progress" id="fc_progress">Select a quiz session to begin</div>
          <div id="fc_quiz_notify" style="margin-top:10px; font-weight:900; color:#1e3a8a;"></div>

<div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:14px;">
            <button class="fc-btn fc-btn-secondary" type="button" id="fc_save_progress" disabled> <i class="fa-solid fa-floppy-disk"></i> Save Progress</button>
            <button class="fc-btn fc-btn-primary" type="button" id="fc_finish" disabled> <i class="fa-solid fa-graduation-cap"></i> Finish & Get Score</button>
            <button class="fc-btn fc-btn-ghost" type="button" id="fc_refresh_session" disabled> <i class="fa-solid fa-rotate"></i> Sync & Reload</button>
          </div>

          <div class="row" style="margin-top:14px">
            <div style="padding:12px; border-radius:14px; background:#f8fafc; border:1px solid #e5e7eb;">
              <div style="font-weight:1000; color:#1e3a8a; margin-bottom:6px;">Media Controls</div>
              <div style="color:#334155; font-weight:700; font-size:.9rem;">
                If visuals/audio/video are attached to questions, you can play them below each prompt.
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="fc-card">
        <div class="fc-card-head">
          <h2>Quiz</h2>
        </div>
        <div class="fc-feedback-card">
          <div id="fc_questions"></div>
          <div id="fc_feedback" style="margin-top:16px"></div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'partials/ai-chat-widget.php'; ?>

  <script>
    window.ROOT_URL = '<?= ROOT_URL ?>';
    window.FANTE_CLASS_ENDPOINTS = {
      listSessions: '<?= ROOT_URL ?>admin/quiz-list-sessions.php',
      loadQuestions: '<?= ROOT_URL ?>admin/quiz-fetch-questions.php',
      saveProgress: '<?= ROOT_URL ?>admin/quiz-save-progress.php',
      mediaBaseUrl: '<?= ROOT_URL ?>uploads/quiz-media/',
      notifyCookieKey: 'fc_quiz_builder_notify'
    };
  </script>

  <script src="<?= ROOT_URL ?>js/fante-class.js"></script>

  <script>
    (function(){
      const cookieStr = document.cookie || '';
      const getCookie = (k) => {
        const parts = ('; ' + cookieStr).split('; ' + k + '=');
        if(parts.length < 2) return null;
        return parts.pop().split(';').shift();
      };

      // Existing builder/session update notice
      const key = (window.FANTE_CLASS_ENDPOINTS && window.FANTE_CLASS_ENDPOINTS.notifyCookieKey) ? window.FANTE_CLASS_ENDPOINTS.notifyCookieKey : 'fc_quiz_builder_notify';
      const builderVal = getCookie(key);
      if(builderVal !== null){
        const el = document.getElementById('fc_quiz_notify');
        if(el){
          el.textContent = 'Quiz session was updated successfully.';
          el.style.color = '#16a34a';
          el.style.opacity = '1';
          document.cookie = key + '=; path=/; max-age=0;';
        }
      }

      // Learner sync/submit notifications
      const syncStatus = getCookie('fc_last_sync_status');
      const syncMsg = getCookie('fc_last_sync_msg');
      const submitStatus = getCookie('fc_last_submit_status');
      const submitMsg = getCookie('fc_last_submit_msg');

      const el2 = document.getElementById('fc_quiz_notify');
      if(el2){
        const hasSync = (syncStatus !== null);
        const hasSubmit = (submitStatus !== null);
        let status = null;
        let msg = null;
        if(hasSubmit){
          status = submitStatus;
          msg = submitMsg ? decodeURIComponent(submitMsg) : null;
        } else if(hasSync){
          status = syncStatus;
          msg = syncMsg ? decodeURIComponent(syncMsg) : null;
        }

        if(status){
          const isOk = String(status).toLowerCase() === 'success';
          el2.textContent = msg || (isOk ? 'Saved successfully.' : 'Action failed.');
          el2.style.color = isOk ? '#16a34a' : '#b91c1c';
          el2.style.opacity = '1';
        }

        if(hasSubmit){
          document.cookie = 'fc_last_submit_status=; path=/; max-age=0;';
          document.cookie = 'fc_last_submit_msg=; path=/; max-age=0;';
        }
        if(hasSync){
          document.cookie = 'fc_last_sync_status=; path=/; max-age=0;';
          document.cookie = 'fc_last_sync_msg=; path=/; max-age=0;';
        }
      }
    })();
  </script>


  <script>
    // Enable buttons when a quiz is started
    document.addEventListener('click', (e) => {
      if (e.target && e.target.id === 'fc_start') {
        setTimeout(() => {
          document.getElementById('fc_save_progress')?.removeAttribute('disabled');
          document.getElementById('fc_finish')?.removeAttribute('disabled');
        }, 50);
      }
    });
  </script>
</section>

  <?php include 'partials/footer.php'; ?>
</body>
</html>

