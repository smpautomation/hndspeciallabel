<template>
    <div class="page-wrapper">
      <!-- Background atmosphere -->
      <div class="bg-layer" aria-hidden="true">
        <div class="bg-grid"></div>
        <div class="bg-glow"></div>
      </div>

      <!-- Header -->
      <header class="site-header" :class="{ 'fade-in': mounted }">
        <div class="header-inner">
          <div class="brand">
            <span class="brand-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="6" width="24" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                <line x1="7" y1="10" x2="7" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="10" y1="10" x2="10" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="13" y1="10" x2="13" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="16" y1="10" x2="16" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="19" y1="10" x2="19" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </span>
            <div>
              <span class="brand-name">LABEL<span class="brand-accent">PRINT</span></span>
              <span class="brand-sub">Special Label Station</span>
            </div>
          </div>
          <div class="user-ip-badge" title="Your device IP address">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
            {{ userIp || 'Detecting IP...' }}
          </div>
        </div>
      </header>

      <!-- Flash messages -->
      <transition name="flash">
        <div v-if="flashError" class="flash-banner flash-error" role="alert">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ flashError }}</span>
          <button class="flash-close" @click="flashError = null" aria-label="Dismiss">✕</button>
        </div>
      </transition>

      <transition name="flash">
        <div v-if="flashSuccess" class="flash-banner flash-success" role="alert">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
          <span>{{ flashSuccess }}</span>
          <button class="flash-close" @click="flashSuccess = null" aria-label="Dismiss">✕</button>
        </div>
      </transition>

      <!-- No printer warning -->
      <transition name="flash">
        <div v-if="noPrinterFound && !settingsOpen" class="flash-banner flash-warning" role="alert">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <span>No printer found for your IP. Please configure one using the <strong>Settings</strong> button.</span>
          <button class="flash-close" @click="noPrinterFound = false" aria-label="Dismiss">✕</button>
        </div>
      </transition>

      <!-- Main content -->
      <main class="main-content">
        <div class="form-card" :class="{ 'slide-up': mounted }">
          <!-- Label tape perforation top border -->
          <div class="tape-perforations" aria-hidden="true">
            <span v-for="n in 28" :key="n" class="perf-hole"></span>
          </div>

          <div class="card-body">
            <div class="card-heading">
              <h1 class="card-title">Print Special Label</h1>
              <p class="card-subtitle">Fill in the details below and submit to send to your printer.</p>
            </div>

            <!-- Printer status indicator -->
            <div class="printer-status" :class="printerStatusClass">
              <span class="status-dot"></span>
              <span class="status-text">{{ printerStatusText }}</span>
              <span v-if="printerConfig.satoIp" class="status-ip">{{ printerConfig.satoIp }}</span>
            </div>

            <form @submit.prevent="submitForm" novalidate>

              <!-- Model Name -->
              <div class="field-group" :class="{ 'field-error': errors.model_name }">
                <label for="model_name" class="field-label">
                  Model Name
                  <span class="field-required" aria-label="required">*</span>
                </label>
                <div class="select-wrapper">
                  <select
                    id="model_name"
                    v-model="form.model_name"
                    class="field-select"
                    :disabled="submitting"
                    @change="onModelChange"
                    aria-describedby="model_name_error"
                  >
                    <option value="" disabled>— Select a model —</option>
                    <option
                      v-for="model in models"
                      :key="model.model_name"
                      :value="model.model_name"
                      :data-fixed="model.fixed_values"
                    >
                      {{ model.model_name }}
                    </option>
                  </select>
                  <span class="select-arrow" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                  </span>
                </div>
                <span v-if="errors.model_name" id="model_name_error" class="error-msg" role="alert">{{ errors.model_name }}</span>
              </div>

              <!-- Shipping Date -->
              <div class="field-group" :class="{ 'field-error': errors.shipping_date }">
                <label for="shipping_date" class="field-label">
                  Shipping Date
                  <span class="field-required" aria-label="required">*</span>
                </label>
                <div class="input-wrapper">
                  <span class="input-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  </span>
                  <input
                    id="shipping_date"
                    type="date"
                    v-model="form.shipping_date"
                    class="field-input field-input--icon"
                    :disabled="submitting"
                    aria-describedby="shipping_date_error"
                  />
                </div>
                <span v-if="errors.shipping_date" id="shipping_date_error" class="error-msg" role="alert">{{ errors.shipping_date }}</span>
              </div>

              <!-- Quantity + Print Quantity side by side -->
              <div class="qty-row">
                <!-- Quantity (units in pallet) -->
                <div class="field-group" :class="{ 'field-error': errors.quantity }">
                  <label for="quantity" class="field-label">
                    Quantity
                    <span class="field-required" aria-label="required">*</span>
                  </label>
                  <div class="input-wrapper">
                    <span class="input-icon" aria-hidden="true">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-4 0v2"/><path d="M8 7V5a2 2 0 00-4 0v2"/></svg>
                    </span>
                    <input
                      id="quantity"
                      type="number"
                      v-model.number="form.quantity"
                      class="field-input field-input--icon"
                      min="1"
                      max="9999"
                      placeholder="e.g. 100"
                      :disabled="submitting"
                      aria-describedby="quantity_error quantity_hint"
                    />
                  </div>
                  <span id="quantity_hint" class="field-hint-text">Units inside the pallet</span>
                  <span v-if="errors.quantity" id="quantity_error" class="error-msg" role="alert">{{ errors.quantity }}</span>
                </div>

                <!-- Print Quantity (copies to print) -->
                <div class="field-group" :class="{ 'field-error': errors.print_quantity }">
                  <label for="print_quantity" class="field-label">
                    Print Quantity
                    <span class="field-required" aria-label="required">*</span>
                  </label>
                  <div class="input-wrapper">
                    <span class="input-icon" aria-hidden="true">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    </span>
                    <input
                      id="print_quantity"
                      type="number"
                      v-model.number="form.print_quantity"
                      class="field-input field-input--icon"
                      min="1"
                      max="999"
                      placeholder="e.g. 1"
                      :disabled="submitting"
                      aria-describedby="print_quantity_error print_quantity_hint"
                    />
                  </div>
                  <span id="print_quantity_hint" class="field-hint-text">Number of labels to print</span>
                  <span v-if="errors.print_quantity" id="print_quantity_error" class="error-msg" role="alert">{{ errors.print_quantity }}</span>
                </div>
              </div>

              <!-- Custom Label Checkbox -->
              <div class="field-group checkbox-group">
                <label class="checkbox-label" for="is_custom">
                  <input
                    id="is_custom"
                    type="checkbox"
                    v-model="form.is_custom"
                    class="checkbox-input"
                    :disabled="submitting"
                  />
                  <span class="checkbox-box" aria-hidden="true">
                    <svg v-if="form.is_custom" width="11" height="11" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 6 5 9 10 3"/></svg>
                  </span>
                  <span class="checkbox-text">
                    Print as <strong>Custom Label</strong>
                    <span class="checkbox-sub">Check this if the label requires custom quantity</span>
                  </span>
                </label>
              </div>

              <!-- Submit button -->
              <button
                type="submit"
                class="submit-btn"
                :disabled="submitting || !printerConfig.satoIp"
                :class="{ 'is-loading': submitting }"
              >
                <span v-if="!submitting" class="btn-content">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                  Send to Printer
                </span>
                <span v-else class="btn-content">
                  <svg class="spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/></svg>
                  Sending to Printer...
                </span>
              </button>

              <p v-if="!printerConfig.satoIp" class="no-printer-hint">
                ⚙️ Open <strong>Settings</strong> (bottom-right) to configure your printer before printing.
              </p>
            </form>
          </div>

          <!-- Label tape perforation bottom border -->
          <div class="tape-perforations tape-perforations--bottom" aria-hidden="true">
            <span v-for="n in 28" :key="n" class="perf-hole"></span>
          </div>
        </div>
      </main>

      <!-- Settings Floating Button -->
      <button
        class="fab-settings"
        @click="settingsOpen = true"
        :class="{ 'fab-pulse': noPrinterFound }"
        aria-label="Open printer settings"
        title="Printer Settings"
      >
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="3"/>
          <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
        </svg>
      </button>

      <!-- Settings Drawer Overlay -->
      <transition name="overlay">
        <div v-if="settingsOpen" class="drawer-overlay" @click.self="closeSettings" aria-hidden="true"></div>
      </transition>

      <!-- Settings Drawer -->
      <transition name="drawer">
        <aside v-if="settingsOpen" class="settings-drawer" role="dialog" aria-modal="true" aria-label="Printer Settings">
          <div class="drawer-header">
            <div>
              <h2 class="drawer-title">Printer Settings</h2>
              <p class="drawer-subtitle">Configure the printer for your workstation</p>
            </div>
            <button class="drawer-close" @click="closeSettings" aria-label="Close settings">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <div class="drawer-body">
            <!-- Your IP (read-only) -->
            <div class="drawer-info-block">
              <span class="drawer-info-label">Your Workstation IP</span>
              <span class="drawer-info-value">{{ userIp || 'Detecting...' }}</span>
            </div>

            <div class="drawer-divider"></div>

            <!-- SATO IP -->
            <div class="field-group" :class="{ 'field-error': settingsErrors.sato_ip }">
              <label for="sato_ip" class="field-label">SATO Printer IP Address</label>
              <div class="input-wrapper">
                <span class="input-icon" aria-hidden="true">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-4 0v2"/><path d="M8 7V5a2 2 0 00-4 0v2"/></svg>
                </span>
                <input
                  id="sato_ip"
                  type="text"
                  v-model="settingsForm.sato_ip"
                  class="field-input field-input--icon"
                  placeholder="e.g. 192.168.1.50"
                  inputmode="decimal"
                  aria-describedby="sato_ip_error"
                />
              </div>
              <span v-if="settingsErrors.sato_ip" id="sato_ip_error" class="error-msg" role="alert">{{ settingsErrors.sato_ip }}</span>
            </div>

            <!-- Offsets -->
            <div class="field-group offset-row">
              <div class="field-group" :class="{ 'field-error': settingsErrors.horizontal_offset }">
                <label for="h_offset" class="field-label">Horizontal Offset <span class="field-hint">(mm)</span></label>
                <input id="h_offset" type="number" v-model.number="settingsForm.horizontal_offset" class="field-input" placeholder="0" step="0.1" aria-describedby="h_offset_error"/>
                <span v-if="settingsErrors.horizontal_offset" id="h_offset_error" class="error-msg" role="alert">{{ settingsErrors.horizontal_offset }}</span>
              </div>
              <div class="field-group" :class="{ 'field-error': settingsErrors.vertical_offset }">
                <label for="v_offset" class="field-label">Vertical Offset <span class="field-hint">(mm)</span></label>
                <input id="v_offset" type="number" v-model.number="settingsForm.vertical_offset" class="field-input" placeholder="0" step="0.1" aria-describedby="v_offset_error"/>
                <span v-if="settingsErrors.vertical_offset" id="v_offset_error" class="error-msg" role="alert">{{ settingsErrors.vertical_offset }}</span>
              </div>
            </div>

            <p class="offset-help">Offsets adjust where content is printed on the label. Leave at <strong>0</strong> if unsure.</p>

            <div class="drawer-divider" style="margin-top:20px;"></div>

            <!-- Admin Access -->
            <div class="admin-access-block">
              <div class="admin-access-info">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <div>
                  <span class="admin-access-label">Admin Panel</span>
                  <span class="admin-access-desc">Manage models &amp; fixed values</span>
                </div>
              </div>
              <button class="btn-admin" @click="openAdminPrompt">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                Enter
              </button>
            </div>
          </div>

          <div class="drawer-footer">
            <button class="btn-secondary" @click="closeSettings">Cancel</button>
            <button class="btn-primary" @click="saveSettings" :disabled="savingSettings">
              <span v-if="savingSettings">Saving...</span>
              <span v-else>Save Settings</span>
            </button>
          </div>
        </aside>
      </transition>

      <!-- Admin Password Modal -->
      <transition name="flash">
        <div v-if="adminPromptOpen" class="modal-overlay" @click.self="closeAdminPrompt" role="dialog" aria-modal="true" aria-label="Admin Access">
          <div class="modal-box">
            <div class="modal-header">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <h3 class="modal-title">Admin Access</h3>
            </div>
            <p class="modal-desc">Enter the admin password to continue to the model management panel.</p>
            <div class="field-group" :class="{ 'field-error': adminPasswordError }">
              <label for="admin_password" class="field-label">Password</label>
              <div class="input-wrapper">
                <span class="input-icon" aria-hidden="true">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </span>
                <input
                  id="admin_password"
                  :type="showAdminPassword ? 'text' : 'password'"
                  v-model="adminPassword"
                  class="field-input field-input--icon field-input--icon-right"
                  placeholder="Enter admin password"
                  @keyup.enter="submitAdminPassword"
                  autocomplete="current-password"
                />
                <button type="button" class="input-icon-right" @click="showAdminPassword = !showAdminPassword" aria-label="Toggle password visibility">
                  <svg v-if="!showAdminPassword" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
              <span v-if="adminPasswordError" class="error-msg" role="alert">{{ adminPasswordError }}</span>
            </div>
            <div class="modal-footer">
              <button class="btn-secondary" @click="closeAdminPrompt">Cancel</button>
              <button class="btn-primary" @click="submitAdminPassword" :disabled="checkingPassword">
                <span v-if="checkingPassword">Checking...</span>
                <span v-else>Continue</span>
              </button>
            </div>
          </div>
        </div>
      </transition>

    </div>
  </template>

  <script>
  import { defineComponent, ref, computed, onMounted } from 'vue'
  import { usePage, router } from '@inertiajs/vue3'

  export default defineComponent({
    name: 'HNDSpecialLabel',

    props: {
      models:        { type: Array,  default: () => [] },
      userIp:        { type: String, default: null },
      printerConfig: {
        type: Object,
        default: () => ({ satoIp: null, horizontalOffset: 0, verticalOffset: 0 })
      }
    },

    setup(props) {
      const page = usePage()

      const mounted        = ref(false)
      const settingsOpen   = ref(false)
      const submitting     = ref(false)
      const savingSettings = ref(false)
      const noPrinterFound = ref(!props.printerConfig?.satoIp)

      const flashError   = ref(page.props.flash?.error   || null)
      const flashSuccess = ref(page.props.flash?.success || null)

      // ── Main form ────────────────────────────────────────────────────
      const form = ref({
        model_name:     '',
        fixed_values:   '',
        shipping_date:  '',
        quantity:       null,
        print_quantity: 1,
        is_custom:      false
      })

      const errors = ref({
        model_name:     '',
        shipping_date:  '',
        quantity:       '',
        print_quantity: ''
      })

      // ── Settings form ────────────────────────────────────────────────
      const settingsForm = ref({
        sato_ip:           props.printerConfig?.satoIp           || '',
        horizontal_offset: props.printerConfig?.horizontalOffset ?? 0,
        vertical_offset:   props.printerConfig?.verticalOffset   ?? 0
      })

      const settingsErrors = ref({ sato_ip: '', horizontal_offset: '', vertical_offset: '' })

      // ── Printer status ───────────────────────────────────────────────
      const printerConfig = ref({ ...props.printerConfig })

      const printerStatusClass = computed(() =>
        printerConfig.value.satoIp ? 'status--online' : 'status--offline'
      )
      const printerStatusText = computed(() =>
        printerConfig.value.satoIp ? 'Printer ready' : 'No printer configured'
      )

      // ── Admin modal ──────────────────────────────────────────────────
      const adminPromptOpen    = ref(false)
      const adminPassword      = ref('')
      const adminPasswordError = ref('')
      const showAdminPassword  = ref(false)
      const checkingPassword   = ref(false)

      // ── Lifecycle ────────────────────────────────────────────────────
      onMounted(() => {
        setTimeout(() => { mounted.value = true }, 50)
        if (page.props.flash?.error)   flashError.value   = page.props.flash.error
        if (page.props.flash?.success) flashSuccess.value = page.props.flash.success
      })

      // ── Form methods ─────────────────────────────────────────────────
      const onModelChange = () => {
        const selected = props.models.find(m => m.model_name === form.value.model_name)
        if (selected) form.value.fixed_values = selected.fixed_values
        errors.value.model_name = ''
      }

      const validateForm = () => {
        let valid = true
        errors.value = { model_name: '', shipping_date: '', quantity: '', print_quantity: '' }

        if (!form.value.model_name) {
          errors.value.model_name = 'Please select a model name.'
          valid = false
        }
        if (!form.value.shipping_date) {
          errors.value.shipping_date = 'Please enter a shipping date.'
          valid = false
        }
        if (!form.value.quantity || form.value.quantity < 1) {
          errors.value.quantity = 'Please enter a quantity of at least 1.'
          valid = false
        }
        if (!form.value.print_quantity || form.value.print_quantity < 1) {
          errors.value.print_quantity = 'Please enter how many labels to print.'
          valid = false
        }
        return valid
      }

      const submitForm = () => {
        if (!validateForm()) return
        submitting.value  = true
        flashError.value  = null
        flashSuccess.value = null

        router.post(route('print'), {
          model_name:     form.value.model_name,
          fixed_values:   form.value.fixed_values,
          shipping_date:  form.value.shipping_date,
          quantity:       form.value.quantity,
          print_quantity: form.value.print_quantity,
          is_custom:      form.value.is_custom        // ← sent to controller
        }, {
          onSuccess: () => {
            flashSuccess.value = 'Label Sent to Printer!'
            form.value = {
              model_name: '', fixed_values: '', shipping_date: '',
              quantity: null, print_quantity: 1, is_custom: false
            }
          },
          onError: (pageErrors) => {
            if (pageErrors.model_name)     errors.value.model_name     = pageErrors.model_name
            if (pageErrors.shipping_date)  errors.value.shipping_date  = pageErrors.shipping_date
            if (pageErrors.quantity)       errors.value.quantity       = pageErrors.quantity
            if (pageErrors.print_quantity) errors.value.print_quantity = pageErrors.print_quantity
            if (pageErrors.error)          flashError.value            = pageErrors.error
          },
          onFinish: () => {
            submitting.value = false
            if (page.props.flash?.error)   flashError.value   = page.props.flash.error
            if (page.props.flash?.success) flashSuccess.value = page.props.flash.success
          }
        })
      }

      // ── Settings methods ─────────────────────────────────────────────
      const validateSettings = () => {
        let valid = true
        settingsErrors.value = { sato_ip: '', horizontal_offset: '', vertical_offset: '' }
        const ipRegex = /^(\d{1,3}\.){3}\d{1,3}$/
        if (!settingsForm.value.sato_ip || !ipRegex.test(settingsForm.value.sato_ip)) {
          settingsErrors.value.sato_ip = 'Please enter a valid IP address (e.g. 192.168.1.50).'
          valid = false
        }
        return valid
      }

      const saveSettings = () => {
        if (!validateSettings()) return
        savingSettings.value = true

        router.post(route('settings'), {
          sato_ip:           settingsForm.value.sato_ip,
          horizontal_offset: settingsForm.value.horizontal_offset,
          vertical_offset:   settingsForm.value.vertical_offset
        }, {
          onSuccess: () => {
            printerConfig.value.satoIp           = settingsForm.value.sato_ip
            printerConfig.value.horizontalOffset = settingsForm.value.horizontal_offset
            printerConfig.value.verticalOffset   = settingsForm.value.vertical_offset
            noPrinterFound.value = false
            flashSuccess.value   = 'Printer settings saved.'
            settingsOpen.value   = false
          },
          onError: (errs) => { if (errs.sato_ip) settingsErrors.value.sato_ip = errs.sato_ip },
          onFinish: () => { savingSettings.value = false }
        })
      }

      const closeSettings = () => {
        settingsOpen.value = false
        settingsErrors.value = { sato_ip: '', horizontal_offset: '', vertical_offset: '' }
      }

      // ── Admin methods ────────────────────────────────────────────────
      const openAdminPrompt = () => {
        adminPromptOpen.value    = true
        adminPassword.value      = ''
        adminPasswordError.value = ''
        showAdminPassword.value  = false
      }

      const closeAdminPrompt = () => {
        adminPromptOpen.value    = false
        adminPassword.value      = ''
        adminPasswordError.value = ''
      }

      const submitAdminPassword = () => {
        if (!adminPassword.value) {
          adminPasswordError.value = 'Please enter the admin password.'
          return
        }
        checkingPassword.value   = true
        adminPasswordError.value = ''

        router.post(route('admin.verify'), { password: adminPassword.value }, {
          onSuccess: () => {
            checkingPassword.value = false
            closeAdminPrompt()
            router.visit(route('admin'))
          },
          onError: (errs) => {
            checkingPassword.value   = false
            adminPasswordError.value = errs.password || 'Incorrect password. Please try again.'
          }
        })
      }

      return {
        mounted, settingsOpen, submitting, savingSettings, noPrinterFound,
        flashError, flashSuccess,
        form, errors,
        settingsForm, settingsErrors,
        printerConfig, printerStatusClass, printerStatusText,
        adminPromptOpen, adminPassword, adminPasswordError,
        showAdminPassword, checkingPassword,
        onModelChange, submitForm,
        saveSettings, closeSettings,
        openAdminPrompt, closeAdminPrompt, submitAdminPassword
      }
    }
  })
  </script>

  <style scoped>
  /* @import url('https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800&family=Barlow:wght@400;500;600&display=swap'); */

  /* ─── Design Tokens ─────────────────────────────────────────────────── */
  :root,
  .page-wrapper {
    --bg-deep:        #0D1B2A;
    --bg-surface:     #162435;
    --bg-raised:      #1E3247;
    --border:         #2A4460;
    --border-light:   #3A5A7A;
    --amber:          #F5A623;
    --amber-dark:     #C9841A;
    --amber-glow:     rgba(245, 166, 35, 0.15);
    --text-primary:   #EDF2F4;
    --text-secondary: #8FA8C0;
    --text-muted:     #5A7A96;
    --success:        #3DD68C;
    --success-bg:     rgba(61, 214, 140, 0.1);
    --error:          #F06060;
    --error-bg:       rgba(240, 96, 96, 0.1);
    --warning:        #FFD166;
    --warning-bg:     rgba(255, 209, 102, 0.1);
    --radius-sm:      6px;
    --radius-md:      10px;
    --radius-lg:      16px;
    --font-display:   'Barlow Condensed', sans-serif;
    --font-body:      'Barlow', sans-serif;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  .page-wrapper {
    min-height: 100vh;
    background-color: var(--bg-deep);
    font-family: var(--font-body);
    color: var(--text-primary);
    position: relative;
    overflow-x: hidden;
  }

  /* ─── Background ────────────────────────────────────────────────────── */
  .bg-layer { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
  .bg-grid {
    position: absolute; inset: 0;
    background-image:
      linear-gradient(rgba(42, 68, 96, 0.4) 1px, transparent 1px),
      linear-gradient(90deg, rgba(42, 68, 96, 0.4) 1px, transparent 1px);
    background-size: 40px 40px;
    mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%);
  }
  .bg-glow {
    position: absolute; top: -20%; left: 50%; transform: translateX(-50%);
    width: 600px; height: 400px;
    background: radial-gradient(ellipse, rgba(245, 166, 35, 0.06) 0%, transparent 70%);
  }

  /* ─── Header ────────────────────────────────────────────────────────── */
  .site-header {
    position: relative; z-index: 10;
    border-bottom: 1px solid var(--border);
    background: rgba(13, 27, 42, 0.85);
    backdrop-filter: blur(12px);
    opacity: 0; transform: translateY(-8px);
    transition: opacity 0.5s ease, transform 0.5s ease;
  }
  .site-header.fade-in { opacity: 1; transform: translateY(0); }
  .header-inner {
    max-width: 640px; margin: 0 auto; padding: 14px 24px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
  }
  .brand { display: flex; align-items: center; gap: 12px; }
  .brand-icon { color: var(--amber); display: flex; align-items: center; }
  .brand-name {
    display: block; font-family: var(--font-display); font-size: 20px;
    font-weight: 800; letter-spacing: 0.08em; color: var(--text-primary); line-height: 1;
  }
  .brand-accent { color: var(--amber); }
  .brand-sub {
    display: block; font-family: var(--font-body); font-size: 11px;
    color: var(--text-muted); letter-spacing: 0.05em; margin-top: 2px;
  }
  .user-ip-badge {
    display: flex; align-items: center; gap: 6px;
    font-family: 'Courier New', monospace; font-size: 12px;
    color: var(--text-secondary); background: var(--bg-raised);
    border: 1px solid var(--border); padding: 5px 10px;
    border-radius: 20px; white-space: nowrap;
  }

  /* ─── Flash Banners ─────────────────────────────────────────────────── */
  .flash-banner {
    position: relative; z-index: 20;
    max-width: 640px; margin: 16px auto 0;
    padding: 12px 16px 12px 14px; border-radius: var(--radius-md);
    display: flex; align-items: flex-start; gap: 10px;
    font-size: 14px; font-weight: 500; line-height: 1.5;
  }
  .flash-error   { background: var(--error-bg);   border: 1px solid var(--error);   color: var(--error); }
  .flash-success { background: var(--success-bg); border: 1px solid var(--success); color: var(--success); }
  .flash-warning { background: var(--warning-bg); border: 1px solid var(--warning); color: var(--warning); }
  .flash-banner svg { flex-shrink: 0; margin-top: 1px; }
  .flash-banner span { flex: 1; }
  .flash-banner strong { color: inherit; }
  .flash-close {
    background: none; border: none; cursor: pointer; color: inherit;
    opacity: 0.6; font-size: 14px; padding: 0 4px; line-height: 1;
    flex-shrink: 0; transition: opacity 0.2s;
  }
  .flash-close:hover { opacity: 1; }
  .flash-enter-active, .flash-leave-active { transition: all 0.3s ease; }
  .flash-enter-from, .flash-leave-to { opacity: 0; transform: translateY(-6px); }

  /* ─── Main ──────────────────────────────────────────────────────────── */
  .main-content {
    position: relative; z-index: 5;
    padding: 36px 20px 100px; max-width: 640px; margin: 0 auto;
  }

  /* ─── Form Card ─────────────────────────────────────────────────────── */
  .form-card {
    background: var(--bg-surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.03) inset;
    opacity: 0; transform: translateY(20px);
    transition: opacity 0.6s ease 0.2s, transform 0.6s ease 0.2s;
  }
  .form-card.slide-up { opacity: 1; transform: translateY(0); }

  /* ─── Tape Perforations ─────────────────────────────────────────────── */
  .tape-perforations {
    display: flex; align-items: center; gap: 0;
    background: var(--bg-deep); padding: 8px 10px;
    border-bottom: 1px solid var(--border); overflow: hidden;
  }
  .tape-perforations--bottom { border-bottom: none; border-top: 1px solid var(--border); }
  .perf-hole {
    display: inline-block; width: 14px; height: 14px; border-radius: 50%;
    background: var(--bg-raised); border: 1px solid var(--border-light);
    flex-shrink: 0; margin: 0 3px;
  }

  /* ─── Card Body ─────────────────────────────────────────────────────── */
  .card-body { padding: 28px 32px 32px; }
  .card-heading { margin-bottom: 20px; }
  .card-title {
    font-family: var(--font-display); font-size: 28px; font-weight: 800;
    letter-spacing: 0.04em; color: var(--text-primary); line-height: 1.1;
  }
  .card-subtitle { margin-top: 6px; font-size: 14px; color: var(--text-secondary); line-height: 1.5; }

  /* ─── Printer Status ────────────────────────────────────────────────── */
  .printer-status {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 14px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 500; margin-bottom: 24px; border: 1px solid transparent;
  }
  .status--online  { background: var(--success-bg); border-color: rgba(61,214,140,0.25); color: var(--success); }
  .status--offline { background: var(--error-bg);   border-color: rgba(240,96,96,0.25);  color: var(--error); }
  .status-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
  .status--online .status-dot { animation: pulse-dot 2s ease-in-out infinite; }
  .status-text { flex: 1; }
  .status-ip { font-family: 'Courier New', monospace; font-size: 12px; opacity: 0.8; }
  @keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.4; transform: scale(0.8); }
  }

  /* ─── Fields ────────────────────────────────────────────────────────── */
  .field-group { margin-bottom: 20px; }
  .field-label {
    display: block; font-size: 13px; font-weight: 600; letter-spacing: 0.04em;
    color: var(--text-secondary); text-transform: uppercase; margin-bottom: 7px;
  }
  .field-required { color: var(--amber); margin-left: 3px; }
  .field-hint { font-weight: 400; text-transform: none; color: var(--text-muted); font-size: 12px; margin-left: 4px; }

  .select-wrapper, .input-wrapper { position: relative; }
  .select-arrow {
    position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
    pointer-events: none; color: var(--text-muted);
  }
  .input-icon {
    position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    pointer-events: none; color: var(--text-muted);
  }

  .field-select,
  .field-input {
    width: 100%; background: var(--bg-raised); border: 1.5px solid var(--border);
    color: var(--text-primary); font-family: var(--font-body); font-size: 15px;
    padding: 12px 16px; border-radius: var(--radius-md); outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    appearance: none; -webkit-appearance: none;
  }
  .field-select { padding-right: 40px; }
  .field-input--icon { padding-left: 42px; }
  .field-input--icon-right { padding-right: 42px; }

  .field-select:focus, .field-input:focus {
    border-color: var(--amber); box-shadow: 0 0 0 3px var(--amber-glow); background: var(--bg-deep);
  }
  .field-select:disabled, .field-input:disabled { opacity: 0.5; cursor: not-allowed; }

  .field-input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(0.6); cursor: pointer; }
  .field-input[type="number"]::-webkit-inner-spin-button,
  .field-input[type="number"]::-webkit-outer-spin-button { opacity: 0.4; }
  .field-select option { background: var(--bg-raised); color: var(--text-primary); }

  .field-group.field-error .field-select,
  .field-group.field-error .field-input { border-color: var(--error); }
  .error-msg { display: block; margin-top: 6px; font-size: 12px; color: var(--error); }
  .field-hint-text { display: block; margin-top: 5px; font-size: 11px; color: var(--text-muted); line-height: 1.4; }

  /* ─── Custom Checkbox ───────────────────────────────────────────────── */
  .checkbox-group { margin-bottom: 16px; }

  .checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    padding: 14px 16px;
    background: var(--bg-raised);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    transition: border-color 0.2s, background 0.2s;
    user-select: none;
  }
  .checkbox-label:hover { border-color: var(--border-light); background: rgba(30, 50, 71, 0.8); }

  /* When checked, highlight the whole row with amber tint */
  .checkbox-input:checked ~ .checkbox-box,
  .checkbox-label:has(.checkbox-input:checked) {
    border-color: var(--amber);
    background: rgba(245, 166, 35, 0.06);
  }
  .checkbox-label:has(.checkbox-input:checked) {
    border-color: var(--amber);
    background: rgba(245, 166, 35, 0.06);
  }

  .checkbox-input {
    /* visually hidden but accessible */
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
  }

  .checkbox-box {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    border-radius: 5px;
    border: 2px solid var(--border-light);
    background: var(--bg-deep);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1px;
    transition: border-color 0.2s, background 0.2s;
    color: #0D1B2A;
  }
  .checkbox-label:has(.checkbox-input:checked) .checkbox-box {
    background: var(--amber);
    border-color: var(--amber);
  }

  .checkbox-text {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    line-height: 1.3;
  }
  .checkbox-text strong { color: var(--amber); font-weight: 700; }
  .checkbox-sub {
    display: block;
    font-size: 12px;
    font-weight: 400;
    color: var(--text-muted);
    margin-top: 3px;
  }

  /* ─── Quantity Row ──────────────────────────────────────────────────── */
  .qty-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .qty-row .field-group { margin-bottom: 20px; }

  /* ─── Submit Button ─────────────────────────────────────────────────── */
  .submit-btn {
    width: 100%; padding: 15px 24px; background: var(--amber); color: #0D1B2A;
    font-family: var(--font-display); font-size: 17px; font-weight: 700;
    letter-spacing: 0.08em; text-transform: uppercase; border: none;
    border-radius: var(--radius-md); cursor: pointer;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    margin-top: 8px; display: flex; align-items: center; justify-content: center;
  }
  .btn-content { display: flex; align-items: center; gap: 10px; }
  .submit-btn:hover:not(:disabled) {
    background: var(--amber-dark);
    box-shadow: 0 4px 20px rgba(245, 166, 35, 0.35);
    transform: translateY(-1px);
  }
  .submit-btn:active:not(:disabled) { transform: translateY(0); }
  .submit-btn:disabled { opacity: 0.45; cursor: not-allowed; }

  .no-printer-hint { margin-top: 12px; font-size: 13px; color: var(--text-secondary); text-align: center; line-height: 1.5; }
  .no-printer-hint strong { color: var(--amber); }

  .spin { animation: spin 1s linear infinite; }
  @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

  /* ─── FAB ───────────────────────────────────────────────────────────── */
  .fab-settings {
    position: fixed; bottom: 28px; right: 28px; z-index: 50;
    width: 52px; height: 52px; border-radius: 50%;
    background: #1E3247; background: var(--bg-raised, #1E3247);
    border: 1.5px solid #3A5A7A; border: 1.5px solid var(--border-light, #3A5A7A);
    color: #8FA8C0; color: var(--text-secondary, #8FA8C0);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    transition: color 0.2s, border-color 0.2s, background 0.2s, transform 0.2s;
  }
  .fab-settings:hover {
    color: var(--amber); border-color: var(--amber); background: var(--bg-surface);
    transform: rotate(30deg) scale(1.05);
  }
  .fab-settings.fab-pulse { border-color: var(--amber); color: var(--amber); animation: fab-attention 2s ease-in-out infinite; }
  @keyframes fab-attention {
    0%, 100% { box-shadow: 0 4px 20px rgba(0,0,0,0.4), 0 0 0 0 var(--amber-glow); }
    50%       { box-shadow: 0 4px 20px rgba(0,0,0,0.4), 0 0 0 10px transparent; }
  }

  /* ─── Drawer Overlay ────────────────────────────────────────────────── */
  .drawer-overlay { position: fixed; inset: 0; z-index: 60; background: rgba(0,0,0,0.55); backdrop-filter: blur(3px); }
  .overlay-enter-active, .overlay-leave-active { transition: opacity 0.3s ease; }
  .overlay-enter-from, .overlay-leave-to { opacity: 0; }

  /* ─── Settings Drawer ───────────────────────────────────────────────── */
  .settings-drawer {
    position: fixed; top: 0; right: 0; bottom: 0; z-index: 70;
    width: 360px; max-width: 92vw;
    background: #162435; background: var(--bg-surface, #162435);
    border-left: 1px solid #2A4460; border-left: 1px solid var(--border, #2A4460);
    color: #EDF2F4; color: var(--text-primary, #EDF2F4);
    font-family: 'Barlow', sans-serif; font-family: var(--font-body, 'Barlow', sans-serif);
    display: flex; flex-direction: column; box-shadow: -8px 0 40px rgba(0,0,0,0.4);
  }
  .drawer-enter-active, .drawer-leave-active { transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
  .drawer-enter-from, .drawer-leave-to { transform: translateX(100%); }

  .drawer-header {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
    padding: 24px 24px 20px; border-bottom: 1px solid var(--border); flex-shrink: 0;
  }
  .drawer-title { font-family: var(--font-display); font-size: 22px; font-weight: 700; letter-spacing: 0.04em; color: var(--text-primary); }
  .drawer-subtitle { font-size: 13px; color: var(--text-muted); margin-top: 3px; }
  .drawer-close {
    background: var(--bg-raised); border: 1px solid var(--border); border-radius: var(--radius-sm);
    width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--text-secondary); flex-shrink: 0; transition: color 0.2s, border-color 0.2s;
  }
  .drawer-close:hover { color: var(--text-primary); border-color: var(--border-light); }

  .drawer-body { flex: 1; overflow-y: auto; padding: 24px; }

  .drawer-info-block {
    display: flex; flex-direction: column; gap: 4px;
    padding: 12px 14px; background: var(--bg-raised); border: 1px solid var(--border);
    border-radius: var(--radius-sm); margin-bottom: 20px;
  }
  .drawer-info-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); }
  .drawer-info-value { font-family: 'Courier New', monospace; font-size: 15px; color: var(--text-primary); }

  .drawer-divider { height: 1px; background: var(--border); margin: 0 0 20px; }

  .offset-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 8px !important; }
  .offset-row .field-group { margin-bottom: 0; }

  .offset-help { font-size: 12px; color: var(--text-muted); line-height: 1.5; margin-bottom: 8px; }
  .offset-help strong { color: var(--text-secondary); }

  /* ─── Admin Access Block ────────────────────────────────────────────── */
  .admin-access-block {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 14px; background: rgba(245, 166, 35, 0.05);
    border: 1px solid rgba(245, 166, 35, 0.2); border-radius: var(--radius-md);
  }
  .admin-access-info { display: flex; align-items: center; gap: 10px; color: var(--amber); }
  .admin-access-label {
    display: block; font-size: 13px; font-weight: 700; color: var(--text-primary);
    font-family: var(--font-display); letter-spacing: 0.04em; text-transform: uppercase;
  }
  .admin-access-desc { display: block; font-size: 11px; color: var(--text-muted); margin-top: 1px; }
  .btn-admin {
    display: flex; align-items: center; gap: 6px; padding: 8px 14px;
    background: rgba(245, 166, 35, 0.12); border: 1px solid rgba(245, 166, 35, 0.4);
    border-radius: var(--radius-sm); color: var(--amber);
    font-family: var(--font-body); font-size: 13px; font-weight: 700;
    cursor: pointer; white-space: nowrap; transition: background 0.2s, border-color 0.2s; flex-shrink: 0;
  }
  .btn-admin:hover { background: rgba(245, 166, 35, 0.2); border-color: rgba(245, 166, 35, 0.7); }

  /* ─── Drawer Footer ─────────────────────────────────────────────────── */
  .drawer-footer { display: flex; gap: 12px; padding: 16px 24px; border-top: 1px solid var(--border); flex-shrink: 0; }
  .btn-secondary {
    flex: 1; padding: 11px 16px; background: transparent;
    border: 1.5px solid var(--border-light); border-radius: var(--radius-md);
    color: var(--text-secondary); font-family: var(--font-body); font-size: 14px; font-weight: 600;
    cursor: pointer; transition: border-color 0.2s, color 0.2s;
  }
  .btn-secondary:hover { border-color: var(--text-muted); color: var(--text-primary); }
  .btn-primary {
    flex: 2; padding: 11px 16px; background: var(--amber); border: none;
    border-radius: var(--radius-md); color: #0D1B2A;
    font-family: var(--font-body); font-size: 14px; font-weight: 700;
    cursor: pointer; transition: background 0.2s;
  }
  .btn-primary:hover:not(:disabled) { background: var(--amber-dark); }
  .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

  /* ─── Admin Password Modal ──────────────────────────────────────────── */
  .modal-overlay {
    position: fixed; inset: 0; z-index: 80; background: rgba(0,0,0,0.65);
    backdrop-filter: blur(4px); display: flex; align-items: center;
    justify-content: center; padding: 20px;
  }
  .modal-box {
    background: #162435; background: var(--bg-surface, #162435);
    border: 1px solid #2A4460; border: 1px solid var(--border, #2A4460);
    border-radius: var(--radius-lg); padding: 28px; width: 100%; max-width: 380px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    color: #EDF2F4; color: var(--text-primary, #EDF2F4); font-family: 'Barlow', sans-serif;
  }
  .modal-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; color: var(--amber); }
  .modal-title { font-family: var(--font-display); font-size: 22px; font-weight: 800; letter-spacing: 0.04em; color: var(--text-primary); }
  .modal-desc { font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 20px; }
  .modal-footer { display: flex; gap: 10px; margin-top: 20px; }
  .modal-footer .btn-secondary,
  .modal-footer .btn-primary { flex: 1; }

  .input-icon-right {
    position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: var(--text-muted);
    padding: 0; display: flex; align-items: center; transition: color 0.2s;
  }
  .input-icon-right:hover { color: var(--text-secondary); }

  /* ─── Responsive ────────────────────────────────────────────────────── */
  @media (max-width: 520px) {
    .card-body { padding: 20px 18px 24px; }
    .card-title { font-size: 24px; }
    .header-inner { padding: 12px 16px; }
    .user-ip-badge { display: none; }
    .fab-settings { bottom: 20px; right: 20px; }
    .perf-hole { width: 12px; height: 12px; margin: 0 2px; }
  }
  @media (max-width: 400px) {
    .qty-row { grid-template-columns: 1fr; }
  }
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
  }
  </style>
