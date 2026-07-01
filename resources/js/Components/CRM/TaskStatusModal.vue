<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import InputLabel from '@/Components/Core/InputLabel.vue';
import SelectInput from '@/Components/Core/SelectInput.vue';
import PrimaryButton from '@/Components/Core/PrimaryButton.vue';
import SecondaryButton from '@/Components/Core/SecondaryButton.vue';
import InputError from '@/Components/Core/InputError.vue';
import { wTrans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';

const props = defineProps({
    show: Boolean,
    task: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    status: props.task?.status || 'pending',
});

watch(() => props.show, (isVisible) => {
    if (isVisible && props.task) {
       form.status = props.task.status || 'pending';
        form.clearErrors();
    }
});

const submit = () => {
    if (!props.task) return;

    form.patch(admin.tasks.update.url(props.task.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')" maxWidth="md" :title="__('Change Task Status')">
        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="status" :value="__('Status')" class="mb-2" />
                <SelectInput
                    id="status"
                    v-model="form.status"
                    class="w-full"
                >
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="in_progress">{{ __('In Progress') }}</option>
                    <option value="completed">{{ __('Completed') }}</option>
                    <option value="cancelled">{{ __('Cancelled') }}</option>
                </SelectInput>
                <InputError :message="form.errors.status" class="mt-2" />
            </div>

            <div class="flex justify-end gap-3">
                <SecondaryButton @click="$emit('close')">
                    {{ __('Cancel') }}
                </SecondaryButton>

                <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ __('Update Status') }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
