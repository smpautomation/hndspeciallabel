<template>
    <div class="admin-wrapper">
      <!-- Background -->
      <div class="bg-layer" aria-hidden="true">
        <div class="bg-grid"></div>
      </div>

      <!-- Header -->
      <header class="admin-header" :class="{ 'fade-in': mounted }">
        <div class="header-inner">
          <div class="brand">
            <span class="brand-icon" aria-hidden="true">
              <svg width="26" height="26" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
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
              <span class="brand-sub">Admin — Model Management</span>
            </div>
          </div>
          <a :href="route('hnd')" class="back-btn">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Label Print
          </a>
        </div>
      </header>

      <!-- Flash messages -->
      <transition name="flash">
        <div v-if="flashError" class="flash-banner flash-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ flashError }}</span>
          <button class="flash-close" @click="flashError = null">✕</button>
        </div>
      </transition>
      <transition name="flash">
        <div v-if="flashSuccess" class="flash-banner flash-success" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
          <span>{{ flashSuccess }}</span>
          <button class="flash-close" @click="flashSuccess = null">✕</button>
        </div>
      </transition>

      <!-- Main -->
      <main class="admin-main">

        <!-- Page title + Add button -->
        <div class="page-top" :class="{ 'slide-up': mounted }">
          <div>
            <h1 class="page-title">Models</h1>
            <p class="page-subtitle">{{ models.length }} model{{ models.length !== 1 ? 's' : '' }} registered</p>
          </div>
          <button class="btn-add" @click="openAdd">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Model
          </button>
        </div>

        <!-- Search bar -->
        <div class="search-bar-wrap" :class="{ 'slide-up': mounted }" style="animation-delay: 0.05s">
          <div class="search-bar">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              type="text"
              v-model="search"
              class="search-input"
              placeholder="Search by model name..."
              aria-label="Search models"
            />
            <button v-if="search" class="search-clear" @click="search = ''" aria-label="Clear search">✕</button>
          </div>
        </div>

        <!-- Models table -->
        <div class="table-card" :class="{ 'slide-up': mounted }" style="animation-delay: 0.1s">
          <!-- Empty state -->
          <div v-if="filteredModels.length === 0" class="empty-state">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-4 0v2"/><path d="M8 7V5a2 2 0 00-4 0v2"/></svg>
            <p v-if="search">No models match "<strong>{{ search }}</strong>"</p>
            <p v-else>No models yet. Click <strong>Add Model</strong> to get started.</p>
          </div>

          <!-- Table -->
          <div v-else class="table-wrap">
            <table class="model-table" aria-label="Models list">
              <thead>
                <tr>
                  <th class="col-num">#</th>
                  <th class="col-name">Model Name</th>
                  <th class="col-fixed">Fixed Values</th>
                  <th class="col-actions">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(model, index) in filteredModels"
                  :key="model.id"
                  class="table-row"
                >
                  <td class="col-num cell-num">{{ index + 1 }}</td>
                  <td class="col-name">
                    <span class="model-name-text">{{ model.model_name }}</span>
                  </td>
                  <td class="col-fixed">
                    <code class="fixed-value-code">{{ model.fixed_value || '—' }}</code>
                  </td>
                  <td class="col-actions">
                    <div class="action-btns">
                      <!-- <button class="btn-icon btn-edit" @click="openEdit(model)" title="Edit model">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                      </button> -->
                      <button class="btn-icon btn-delete" @click="confirmDelete(model)" title="Delete model">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>

      <!-- ── Add / Edit Modal ─────────────────────────────────────────── -->
      <transition name="modal">
        <div v-if="modalOpen" class="modal-overlay" @click.self="closeModal" role="dialog" aria-modal="true" :aria-label="isEditing ? 'Edit Model' : 'Add Model'">
          <div class="modal-box">
            <!-- Tape perf top -->
            <div class="modal-tape" aria-hidden="true">
              <span v-for="n in 18" :key="n" class="perf-hole"></span>
            </div>

            <div class="modal-inner">
              <h2 class="modal-title">{{ isEditing ? 'Edit Model' : 'Add New Model' }}</h2>
              <p class="modal-desc">{{ isEditing ? 'Update the model name or fixed values below.' : 'Fill in the details for the new model.' }}</p>

              <!-- Model Name -->
              <div class="field-group" :class="{ 'field-error': modalErrors.model_name }">
                <label for="modal_model_name" class="field-label">
                  Model Name
                  <span class="field-required" aria-label="required">*</span>
                </label>
                <input
                  id="modal_model_name"
                  type="text"
                  v-model="modalForm.model_name"
                  class="field-input"
                  placeholder="e.g. HND-0F00G"
                  :disabled="modalSubmitting"
                  aria-describedby="modal_model_name_error"
                />
                <span v-if="modalErrors.model_name" id="modal_model_name_error" class="error-msg" role="alert">{{ modalErrors.model_name }}</span>
              </div>

              <!-- Fixed Values -->
              <div class="field-group" :class="{ 'field-error': modalErrors.fixed_value }">
                <label for="modal_fixed_value" class="field-label">
                  Fixed Values
                </label>
                <textarea
                  id="modal_fixed_value"
                  v-model="modalForm.fixed_value"
                  class="field-textarea"
                  placeholder="e.g. 60HPX"
                  rows="4"
                  :disabled="modalSubmitting"
                  aria-describedby="modal_fixed_value_error modal_fixed_hint"
                ></textarea>
                <span id="modal_fixed_hint" class="field-hint-text">These values are sent to the SATO printer as part of the label template.</span>
                <span v-if="modalErrors.fixed_value" id="modal_fixed_value_error" class="error-msg" role="alert">{{ modalErrors.fixed_value }}</span>
              </div>

              <div class="modal-footer">
                <button class="btn-secondary" @click="closeModal" :disabled="modalSubmitting">Cancel</button>
                <button class="btn-primary" @click="submitModal" :disabled="modalSubmitting">
                  <span v-if="modalSubmitting">{{ isEditing ? 'Saving...' : 'Adding...' }}</span>
                  <span v-else>{{ isEditing ? 'Save Changes' : 'Add Model' }}</span>
                </button>
              </div>
            </div>

            <!-- Tape perf bottom -->
            <div class="modal-tape modal-tape--bottom" aria-hidden="true">
              <span v-for="n in 18" :key="n" class="perf-hole"></span>
            </div>
          </div>
        </div>
      </transition>

      <!-- ── Delete Confirm Modal ─────────────────────────────────────── -->
      <transition name="modal">
        <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null" role="dialog" aria-modal="true" aria-label="Confirm Delete">
          <div class="modal-box modal-box--sm">
            <div class="modal-inner">
              <div class="delete-icon" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
              </div>
              <h2 class="modal-title">Delete Model?</h2>
              <p class="modal-desc">
                You are about to delete <strong class="delete-name">{{ deleteTarget?.model_name }}</strong>.
                This cannot be undone. Any labels using this model will be affected.
              </p>
              <div class="modal-footer">
                <button class="btn-secondary" @click="deleteTarget = null" :disabled="deleting">Cancel</button>
                <button class="btn-danger" @click="submitDelete" :disabled="deleting">
                  <span v-if="deleting">Deleting...</span>
                  <span v-else>Yes, Delete</span>
                </button>
              </div>
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
    name: 'Admin',

    props: {
      models: {
        type: Array,
        default: () => []
      }
    },

    setup(props) {
      const page = usePage()

      const mounted = ref(false)
      const search = ref('')

      const flashError = ref(page.props.flash?.error || null)
      const flashSuccess = ref(page.props.flash?.success || null)

      // ── Modal state ─────────────────────────────────────────────────
      const modalOpen = ref(false)
      const isEditing = ref(false)
      const editingId = ref(null)
      const modalSubmitting = ref(false)

      const modalForm = ref({ model_name: '', fixed_value: '' })
      const modalErrors = ref({ model_name: '', fixed_value: '' })

      // ── Delete state ────────────────────────────────────────────────
      const deleteTarget = ref(null)
      const deleting = ref(false)

      // ── Computed ─────────────────────────────────────────────────────
      const filteredModels = computed(() => {
        if (!search.value.trim()) return props.models
        const q = search.value.toLowerCase()
        return props.models.filter(m => m.model_name.toLowerCase().includes(q))
      })

      // ── Lifecycle ────────────────────────────────────────────────────
      onMounted(() => {
        setTimeout(() => { mounted.value = true }, 50)
        if (page.props.flash?.error)   flashError.value   = page.props.flash.error
        if (page.props.flash?.success) flashSuccess.value = page.props.flash.success
      })

      // ── Add / Edit helpers ───────────────────────────────────────────
      const openAdd = () => {
        isEditing.value = false
        editingId.value = null
        modalForm.value = { model_name: '', fixed_value: '' }
        modalErrors.value = { model_name: '', fixed_value: '' }
        modalOpen.value = true
      }

      const openEdit = (model) => {
        isEditing.value = true
        editingId.value = model.id
        modalForm.value = { model_name: model.model_name, fixed_value: model.fixed_value || '' }
        modalErrors.value = { model_name: '', fixed_value: '' }
        modalOpen.value = true
      }

      const closeModal = () => {
        modalOpen.value = false
        modalErrors.value = { model_name: '', fixed_value: '' }
      }

      const validateModal = () => {
        let valid = true
        modalErrors.value = { model_name: '', fixed_value: '' }
        if (!modalForm.value.model_name.trim()) {
          modalErrors.value.model_name = 'Model name is required.'
          valid = false
        }
        return valid
      }

      const submitModal = () => {
        if (!validateModal()) return
        modalSubmitting.value = true
        flashError.value = null
        flashSuccess.value = null

        if (isEditing.value) {
          router.put(route('admin.update', editingId.value), modalForm.value, {
            onSuccess: () => {
              flashSuccess.value = 'Model updated successfully.'
              closeModal()
            },
            onError: (errs) => {
              if (errs.model_name)   modalErrors.value.model_name   = errs.model_name
              if (errs.fixed_value) modalErrors.value.fixed_value = errs.fixed_value
              if (errs.error)        flashError.value = errs.error
            },
            onFinish: () => { modalSubmitting.value = false }
          })
        } else {
          router.post(route('admin.store'), modalForm.value, {
            onSuccess: () => {
              flashSuccess.value = 'Model added successfully.'
              closeModal()
            },
            onError: (errs) => {
              if (errs.model_name)   modalErrors.value.model_name   = errs.model_name
              if (errs.fixed_value) modalErrors.value.fixed_value = errs.fixed_value
              if (errs.error)        flashError.value = errs.error
            },
            onFinish: () => { modalSubmitting.value = false }
          })
        }
      }

      // ── Delete helpers ───────────────────────────────────────────────
      const confirmDelete = (model) => {
        deleteTarget.value = model
      }

      const submitDelete = () => {
        if (!deleteTarget.value) return
        deleting.value = true
        flashError.value = null
        flashSuccess.value = null

        router.delete(route('admin.destroy', deleteTarget.value.id), {
          onSuccess: () => {
            flashSuccess.value = `"${deleteTarget.value.model_name}" deleted.`
            deleteTarget.value = null
          },
          onError: (errs) => {
            flashError.value = errs.error || 'Failed to delete model.'
            deleteTarget.value = null
          },
          onFinish: () => { deleting.value = false }
        })
      }

      return {
        mounted,
        search,
        flashError,
        flashSuccess,
        filteredModels,
        modalOpen,
        isEditing,
        modalForm,
        modalErrors,
        modalSubmitting,
        deleteTarget,
        deleting,
        openAdd,
        openEdit,
        closeModal,
        submitModal,
        confirmDelete,
        submitDelete
      }
    }
  })
  </script>

  <style scoped>
  /* @import url('https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800&family=Barlow:wght@400;500;600&display=swap'); */

  /* ─── Tokens ────────────────────────────────────────────────────────── */
  :root,
  .admin-wrapper {
    --bg-deep:       #0D1B2A;
    --bg-surface:    #162435;
    --bg-raised:     #1E3247;
    --border:        #2A4460;
    --border-light:  #3A5A7A;
    --amber:         #F5A623;
    --amber-dark:    #C9841A;
    --amber-glow:    rgba(245, 166, 35, 0.15);
    --text-primary:  #EDF2F4;
    --text-secondary:#8FA8C0;
    --text-muted:    #5A7A96;
    --success:       #3DD68C;
    --success-bg:    rgba(61, 214, 140, 0.1);
    --error:         #F06060;
    --error-bg:      rgba(240, 96, 96, 0.1);
    --radius-sm:     6px;
    --radius-md:     10px;
    --radius-lg:     16px;
    --font-display:  'Barlow Condensed', sans-serif;
    --font-body:     'Barlow', sans-serif;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  .admin-wrapper {
    min-height: 100vh;
    background-color: var(--bg-deep);
    font-family: var(--font-body);
    color: var(--text-primary);
    position: relative;
  }

  /* ─── Background ────────────────────────────────────────────────────── */
  .bg-layer { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
  .bg-grid {
    position: absolute; inset: 0;
    background-image:
      linear-gradient(rgba(42, 68, 96, 0.35) 1px, transparent 1px),
      linear-gradient(90deg, rgba(42, 68, 96, 0.35) 1px, transparent 1px);
    background-size: 40px 40px;
    mask-image: radial-gradient(ellipse 100% 60% at 50% 0%, black 30%, transparent 100%);
  }

  /* ─── Header ────────────────────────────────────────────────────────── */
  .admin-header {
    position: relative; z-index: 10;
    border-bottom: 1px solid var(--border);
    background: rgba(13, 27, 42, 0.9);
    backdrop-filter: blur(12px);
    opacity: 0; transform: translateY(-8px);
    transition: opacity 0.5s ease, transform 0.5s ease;
  }
  .admin-header.fade-in { opacity: 1; transform: translateY(0); }
  .header-inner {
    max-width: 960px; margin: 0 auto;
    padding: 14px 24px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
  }
  .brand { display: flex; align-items: center; gap: 12px; }
  .brand-icon { color: var(--amber); display: flex; align-items: center; }
  .brand-name {
    display: block;
    font-family: var(--font-display); font-size: 20px; font-weight: 800;
    letter-spacing: 0.08em; color: var(--text-primary); line-height: 1;
  }
  .brand-accent { color: var(--amber); }
  .brand-sub {
    display: block; font-family: var(--font-body); font-size: 11px;
    color: var(--text-muted); letter-spacing: 0.05em; margin-top: 2px;
  }
  .back-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 14px;
    background: var(--bg-raised);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--text-secondary);
    font-family: var(--font-body); font-size: 13px; font-weight: 600;
    text-decoration: none;
    transition: color 0.2s, border-color 0.2s;
    white-space: nowrap;
  }
  .back-btn:hover { color: var(--text-primary); border-color: var(--border-light); }

  /* ─── Flash ─────────────────────────────────────────────────────────── */
  .flash-banner {
    position: relative; z-index: 20;
    max-width: 960px; margin: 16px auto 0;
    padding: 11px 14px; border-radius: var(--radius-md);
    display: flex; align-items: center; gap: 10px;
    font-size: 14px; font-weight: 500;
  }
  .flash-error   { background: var(--error-bg);   border: 1px solid var(--error);   color: var(--error); }
  .flash-success { background: var(--success-bg); border: 1px solid var(--success); color: var(--success); }
  .flash-banner span { flex: 1; }
  .flash-close {
    background: none; border: none; cursor: pointer; color: inherit;
    opacity: 0.6; font-size: 13px; padding: 0 4px; transition: opacity 0.2s;
  }
  .flash-close:hover { opacity: 1; }
  .flash-enter-active, .flash-leave-active { transition: all 0.3s ease; }
  .flash-enter-from, .flash-leave-to { opacity: 0; transform: translateY(-6px); }

  /* ─── Main ──────────────────────────────────────────────────────────── */
  .admin-main {
    position: relative; z-index: 5;
    max-width: 960px; margin: 0 auto;
    padding: 32px 20px 80px;
  }

  /* ─── Page top ──────────────────────────────────────────────────────── */
  .page-top {
    display: flex; align-items: flex-end; justify-content: space-between;
    gap: 16px; margin-bottom: 20px;
    opacity: 0; transform: translateY(16px);
    transition: opacity 0.5s ease 0.1s, transform 0.5s ease 0.1s;
  }
  .page-top.slide-up { opacity: 1; transform: translateY(0); }
  .page-title {
    font-family: var(--font-display); font-size: 36px; font-weight: 800;
    letter-spacing: 0.04em; color: var(--text-primary); line-height: 1;
  }
  .page-subtitle { font-size: 13px; color: var(--text-muted); margin-top: 4px; }

  .btn-add {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 20px;
    background: var(--amber); border: none; border-radius: var(--radius-md);
    color: #0D1B2A; font-family: var(--font-display); font-size: 15px;
    font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
    cursor: pointer; white-space: nowrap;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    flex-shrink: 0;
  }
  .btn-add:hover {
    background: var(--amber-dark);
    box-shadow: 0 4px 16px var(--amber-glow);
    transform: translateY(-1px);
  }

  /* ─── Search ────────────────────────────────────────────────────────── */
  .search-bar-wrap {
    margin-bottom: 16px;
    opacity: 0; transform: translateY(14px);
    transition: opacity 0.5s ease 0.15s, transform 0.5s ease 0.15s;
  }
  .search-bar-wrap.slide-up { opacity: 1; transform: translateY(0); }
  .search-bar {
    display: flex; align-items: center; gap: 10px;
    background: var(--bg-surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: 10px 14px;
    color: var(--text-muted);
    transition: border-color 0.2s;
  }
  .search-bar:focus-within { border-color: var(--amber); }
  .search-input {
    flex: 1; background: none; border: none; outline: none;
    color: var(--text-primary); font-family: var(--font-body); font-size: 14px;
  }
  .search-input::placeholder { color: var(--text-muted); }
  .search-clear {
    background: none; border: none; cursor: pointer; color: var(--text-muted);
    font-size: 13px; padding: 0; transition: color 0.2s;
  }
  .search-clear:hover { color: var(--text-primary); }

  /* ─── Table card ────────────────────────────────────────────────────── */
  .table-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.25);
    opacity: 0; transform: translateY(14px);
    transition: opacity 0.5s ease 0.2s, transform 0.5s ease 0.2s;
  }
  .table-card.slide-up { opacity: 1; transform: translateY(0); }

  .empty-state {
    padding: 56px 24px;
    text-align: center;
    color: var(--text-muted);
    display: flex; flex-direction: column; align-items: center; gap: 12px;
  }
  .empty-state p { font-size: 14px; line-height: 1.5; }
  .empty-state strong { color: var(--text-secondary); }

  .table-wrap { overflow-x: auto; }
  .model-table {
    width: 100%; border-collapse: collapse;
    font-size: 14px;
  }
  .model-table thead tr {
    border-bottom: 1px solid var(--border);
    background: var(--bg-raised);
  }
  .model-table th {
    padding: 12px 16px;
    font-family: var(--font-display); font-weight: 700; font-size: 11px;
    letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted);
    text-align: left; white-space: nowrap;
  }
  .col-num     { width: 48px; text-align: center; }
  .col-name    { min-width: 180px; }
  .col-fixed   { min-width: 200px; }
  .col-actions { width: 160px; }

  .table-row { border-bottom: 1px solid var(--border); transition: background 0.15s; }
  .table-row:last-child { border-bottom: none; }
  .table-row:hover { background: rgba(255,255,255,0.02); }

  .model-table td { padding: 14px 16px; vertical-align: middle; }
  .cell-num { text-align: center; color: var(--text-muted); font-size: 12px; }

  .model-name-text {
    font-family: var(--font-display); font-size: 15px; font-weight: 600;
    letter-spacing: 0.02em; color: var(--text-primary);
  }
  .fixed-value-code {
    font-family: 'Courier New', monospace; font-size: 12px;
    color: var(--text-secondary);
    background: var(--bg-raised);
    border: 1px solid var(--border);
    border-radius: 4px; padding: 3px 8px;
    display: inline-block; max-width: 320px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    vertical-align: middle;
  }

  .action-btns { display: flex; gap: 8px; align-items: center; }
  .btn-icon {
    display: flex; align-items: center; gap: 5px;
    padding: 6px 12px; border-radius: var(--radius-sm);
    font-family: var(--font-body); font-size: 12px; font-weight: 600;
    cursor: pointer; border: 1px solid transparent; transition: all 0.15s;
    white-space: nowrap;
  }
  .btn-edit {
    background: rgba(143, 168, 192, 0.1); border-color: rgba(143, 168, 192, 0.25);
    color: var(--text-secondary);
  }
  .btn-edit:hover {
    background: rgba(143, 168, 192, 0.18); border-color: var(--border-light);
    color: var(--text-primary);
  }
  .btn-delete {
    background: rgba(240, 96, 96, 0.08); border-color: rgba(240, 96, 96, 0.2);
    color: var(--error);
  }
  .btn-delete:hover {
    background: rgba(240, 96, 96, 0.16); border-color: var(--error);
  }

  /* ─── Shared field styles ───────────────────────────────────────────── */
  .field-group { margin-bottom: 18px; }
  .field-label {
    display: block; font-size: 12px; font-weight: 700; letter-spacing: 0.05em;
    text-transform: uppercase; color: var(--text-secondary); margin-bottom: 7px;
  }
  .field-required { color: var(--amber); margin-left: 3px; }
  .field-input, .field-textarea {
    width: 100%;
    background: var(--bg-raised); border: 1.5px solid var(--border);
    color: var(--text-primary); font-family: var(--font-body); font-size: 14px;
    padding: 11px 14px; border-radius: var(--radius-md); outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    appearance: none;
  }
  .field-textarea { resize: vertical; min-height: 90px; line-height: 1.6; }
  .field-input:focus, .field-textarea:focus {
    border-color: var(--amber); box-shadow: 0 0 0 3px var(--amber-glow);
  }
  .field-group.field-error .field-input,
  .field-group.field-error .field-textarea { border-color: var(--error); }
  .error-msg { display: block; margin-top: 5px; font-size: 12px; color: var(--error); }
  .field-hint-text { display: block; margin-top: 5px; font-size: 11px; color: var(--text-muted); line-height: 1.4; }

  /* ─── Modal ─────────────────────────────────────────────────────────── */
  .modal-overlay {
    position: fixed; inset: 0; z-index: 80;
    background: rgba(0,0,0,0.65); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center; padding: 20px;
  }
  .modal-box {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    width: 100%; max-width: 460px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    overflow: hidden;
  }
  .modal-box--sm { max-width: 380px; }
  .modal-inner { padding: 24px 28px; }
  .modal-title {
    font-family: var(--font-display); font-size: 24px; font-weight: 800;
    letter-spacing: 0.04em; color: var(--text-primary); margin-bottom: 6px;
  }
  .modal-desc { font-size: 13px; color: var(--text-secondary); margin-bottom: 20px; line-height: 1.5; }
  .modal-footer { display: flex; gap: 10px; margin-top: 4px; }
  .modal-footer .btn-secondary,
  .modal-footer .btn-primary,
  .modal-footer .btn-danger { flex: 1; }

  .modal-tape {
    display: flex; align-items: center;
    background: var(--bg-deep); padding: 7px 10px;
    border-bottom: 1px solid var(--border); overflow: hidden;
  }
  .modal-tape--bottom { border-bottom: none; border-top: 1px solid var(--border); }
  .perf-hole {
    display: inline-block; width: 12px; height: 12px; border-radius: 50%;
    background: var(--bg-raised); border: 1px solid var(--border-light);
    flex-shrink: 0; margin: 0 3px;
  }

  .modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
  .modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.96) translateY(8px); }

  /* Delete modal specifics */
  .delete-icon {
    width: 52px; height: 52px; border-radius: 50%;
    background: var(--error-bg); border: 1px solid rgba(240, 96, 96, 0.25);
    display: flex; align-items: center; justify-content: center;
    color: var(--error); margin-bottom: 14px;
  }
  .delete-name { color: var(--text-primary); }

  /* ─── Shared buttons ────────────────────────────────────────────────── */
  .btn-secondary {
    padding: 11px 16px;
    background: transparent; border: 1.5px solid var(--border-light);
    border-radius: var(--radius-md); color: var(--text-secondary);
    font-family: var(--font-body); font-size: 14px; font-weight: 600;
    cursor: pointer; transition: border-color 0.2s, color 0.2s;
  }
  .btn-secondary:hover:not(:disabled) { border-color: var(--text-muted); color: var(--text-primary); }
  .btn-secondary:disabled { opacity: 0.5; cursor: not-allowed; }

  .btn-primary {
    padding: 11px 16px;
    background: var(--amber); border: none;
    border-radius: var(--radius-md); color: #0D1B2A;
    font-family: var(--font-body); font-size: 14px; font-weight: 700;
    cursor: pointer; transition: background 0.2s;
  }
  .btn-primary:hover:not(:disabled) { background: var(--amber-dark); }
  .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

  .btn-danger {
    padding: 11px 16px;
    background: var(--error); border: none;
    border-radius: var(--radius-md); color: #fff;
    font-family: var(--font-body); font-size: 14px; font-weight: 700;
    cursor: pointer; transition: background 0.2s;
  }
  .btn-danger:hover:not(:disabled) { background: #d94f4f; }
  .btn-danger:disabled { opacity: 0.5; cursor: not-allowed; }

  /* ─── Responsive ────────────────────────────────────────────────────── */
  @media (max-width: 600px) {
    .admin-main { padding: 24px 14px 60px; }
    .page-title { font-size: 28px; }
    .col-fixed { display: none; }
    .header-inner { padding: 12px 14px; }
    .brand-name { font-size: 17px; }
  }

  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
  }
  </style>
