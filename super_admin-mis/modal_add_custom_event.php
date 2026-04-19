<!-- Add Custom Event Modal -->
<div class="modal-overlay" id="addCustomEventModal" style="display: none;">
  <div class="modal-box">
    <div class="modal-header">
      <h2>Add Custom Event</h2>
      <button class="close-button" type="button" aria-label="Close" onclick="closeAddCustomEventModal()">&times;</button>
    </div>
    <form id="addCustomEventForm" autocomplete="off">
      <div class="form-group">
        <label for="eventName">Event Name<span class="required">*</span></label>
        <input type="text" id="eventName" name="eventName" required />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="eventStartDate">Start Date<span class="required">*</span></label>
          <input type="date" id="eventStartDate" name="eventStartDate" required />
        </div>
        <div class="form-group">
          <label for="eventEndDate">End Date<span class="required">*</span></label>
          <input type="date" id="eventEndDate" name="eventEndDate" required />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="eventStartTime">Start Time<span class="required">*</span></label>
          <input type="time" id="eventStartTime" name="eventStartTime" required />
        </div>
        <div class="form-group">
          <label for="eventEndTime">End Time<span class="required">*</span></label>
          <input type="time" id="eventEndTime" name="eventEndTime" required />
        </div>
      </div>
      <div class="form-row custom-event-row">
        <div class="form-group">
          <label for="allDaySwitch">All Day</label>
          <label class="switch">
            <input type="checkbox" id="allDaySwitch" name="allDay">
            <span class="slider"></span>
          </label>
        </div>
        <div class="form-group">
          <label for="eventRepeat">Repeat</label>
          <div class="custom-select-wrapper">
            <select id="eventRepeat" name="eventRepeat">
              <option value="none">Does not repeat</option>
              <option value="daily">Every day</option>
              <option value="weekly">Every week</option>
              <option value="monthly">Every month</option>
              <option value="yearly">Every year</option>
              <option value="custom">Custom...</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="eventColor">Color Selection</label>
          <div class="color-input-wrapper">
            <span class="color-swatch-display" style="background: #0f7a53; border: 2px solid #ccc;"></span>
            <input type="color" id="eventColor" name="eventColor" style="width: 36px; height: 36px; padding: 0; border: none; background: none; position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer;" />
            <input type="text" id="eventColorHex" name="eventColorHex" maxlength="7" placeholder="#RRGGBB or color name" />
          </div>
        </div>
      </div>
      <div class="form-group" id="customRecurrenceOptions" style="display: none;">
        <label>Custom Recurrence</label>
        <div class="custom-recurrence-header-row">
          <div><label>Repeats every</label></div>
          <div><label>Repeats on</label></div>
        </div>
        <div class="custom-recurrence-fields-row">
          <div>
            <input type="number" min="1" id="customEveryNum" name="customEveryNum" style="width: 60px;">
            <div class="custom-select-wrapper" style="width: 110px; display: inline-block;">
              <select id="customEveryUnit" name="customEveryUnit">
                <option value="day">day(s)</option>
                <option value="week">week(s)</option>
                <option value="month">month(s)</option>
                <option value="year">year(s)</option>
              </select>
            </div>
          </div>
          <div>
            <div class="custom-days">
              <button type="button" class="day-btn" data-day="S">S</button>
              <button type="button" class="day-btn" data-day="M">M</button>
              <button type="button" class="day-btn" data-day="T">T</button>
              <button type="button" class="day-btn" data-day="W">W</button>
              <button type="button" class="day-btn" data-day="T">T</button>
              <button type="button" class="day-btn" data-day="F">F</button>
              <button type="button" class="day-btn" data-day="S">S</button>
            </div>
            <input type="hidden" id="customDaysSelected" name="customDaysSelected" value="">
          </div>
        </div>
        <div class="custom-recurrence-ends-row">
          <label>Ends</label>
          <div class="custom-ends-options">
            <label style="display: flex; align-items: center; gap: 8px;"><input type="radio" name="customEnds" value="never" checked>Never</label>
            <label style="display: flex; align-items: center; gap: 8px;"><input type="radio" name="customEnds" value="on">On <input type="date" id="customEndsOn" name="customEndsOn"></label>
            <label style="display: flex; align-items: center; gap: 8px;"><input type="radio" name="customEnds" value="after">After <input type="number" min="1" id="customEndsAfter" name="customEndsAfter" style="width: 60px;"> occurrence(s)</label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="eventDescription">Description</label>
        <textarea id="eventDescription" name="eventDescription" rows="4" placeholder="Event description..."></textarea>
      </div>
      <div class="form-actions">
        <button type="button" class="cancel-btn form-btn-cancel" onclick="closeAddCustomEventModal()">Cancel</button>
        <button type="submit" class="create-btn form-btn-save">Save</button>
      </div>
    </form>
  </div>
</div> 