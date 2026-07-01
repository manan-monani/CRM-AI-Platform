<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import InputLabel from '@/Components/Core/InputLabel.vue';
import SelectInput from '@/Components/Core/SelectInput.vue';
import SearchableSelect from '@/Components/Core/SearchableSelect.vue';
import PrimaryButton from '@/Components/Core/PrimaryButton.vue';
import SecondaryButton from '@/Components/Core/SecondaryButton.vue';
import InputError from '@/Components/Core/InputError.vue';
import { wTrans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';

const props = defineProps({
    show: Boolean,
    deal: {
        type: Object,
        required: true,
    },
    users: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['close']);

const getAssignedToId = (val) => {
    if (val && typeof val === 'object') return String(val.id);
    return val ? String(val) : '';
};

const form = useForm({
    assigned_to: getAssignedToId(props.deal?.assigned_to),
});

import { watch } from 'vue';
watch(() => props.show, (isVisible) => {
    if (isVisible && props.deal) {
        form.assigned_to = getAssignedToId(props.deal.assigned_to);
        form.clearErrors();
    }
});

import { computed } from 'vue';
const userOptions = computed(() => {
    return props.users.map(user => ({
        value: String(user.id),
        label: user.name,
        // Assuming profile_photo_url is available on user object or fallback
        image: user.profile_image || `https://ui-avatars.com/api/?name=${user.name}`
    }));
});

const submit = () => {
    form.patch(admin.deals.update.url(props.deal.id), {
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <form @submit.prevent="submit" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Assign Deal') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Select a sales representative to assign this deal to.') }}
            </p>

            <div class="mt-6">
                <InputLabel for="assigned_to" :value="__('Sales Representative')" class="mb-1" />
                <SearchableSelect
                    v-model="form.assigned_to"
                    :options="userOptions"
                    :placeholder="__('Unassigned')"
                    :error="form.errors.assigned_to"
                />
                <InputError :message="form.errors.assigned_to" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="$emit('close')">
                    {{ __('Cancel') }}
                </SecondaryButton>
                <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ __('Assign Deal') }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
