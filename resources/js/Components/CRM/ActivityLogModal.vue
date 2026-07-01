<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import InputLabel from '@/Components/Core/InputLabel.vue';
import TextInput from '@/Components/Core/TextInput.vue';
import SelectInput from '@/Components/Core/SelectInput.vue';
import TextArea from '@/Components/Core/TextArea.vue';
import PrimaryButton from '@/Components/Core/PrimaryButton.vue';
import SecondaryButton from '@/Components/Core/SecondaryButton.vue';
import InputError from '@/Components/Core/InputError.vue';
import { wTrans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';

const props = defineProps({
    show: Boolean,
    activityableType: String,
    activityableId: Number,
    activity: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    activityable_type: props.activityableType,
    activityable_id: props.activityableId,
    type: 'call',
    subject: '',
    description: '',
    duration_minutes: null,
    outcome: '',
    scheduled_at: null as string | null,
});

const formatDateForInput = (dateString: string | null) => {
    if (!dateString) return null;
    const date = new Date(dateString);
    const offset = date.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(date.getTime() - offset)).toISOString().slice(0, 16);
    return localISOTime;
};

watch(() => props.show, (isVisible) => {
    if (isVisible) {
        if (props.activity) {
            form.activityable_type = props.activity.activityable_type;
            form.activityable_id = props.activity.activityable_id;
            form.type = props.activity.type;
            form.subject = props.activity.subject;
            form.description = props.activity.description;
            form.duration_minutes = props.activity.duration_minutes;
            form.outcome = props.activity.outcome ?? '';
            form.scheduled_at = formatDateForInput(props.activity.scheduled_at);
        } else {
            form.activityable_id = props.activityableId;
            form.activityable_type = props.activityableType;
            form.reset('type', 'subject', 'description', 'duration_minutes', 'outcome', 'scheduled_at');
            form.type = 'call'; // Reset to default
        }
    }
});

const submit = () => {
    if (props.activity) {
         form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(admin.activities.update.url(props.activity.id), {
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    } else {
        form.post(admin.activities.store.url(), {
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    }
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ activity ? __('Edit Activity') : __('Log Activity') }}
            </h2>

            <form @submit.prevent="submit" class="mt-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="type" :value="__('Type')" />
                        <SelectInput
                            id="type"
                            v-model="form.type"
                            class="mt-1 block w-full"
                        >
                            <option value="call">{{ __('Call') }}</option>
                            <option value="email">{{ __('Email') }}</option>
                            <option value="meeting">{{ __('Meeting') }}</option>
                            <option value="note">{{ __('Note') }}</option>
                        </SelectInput>
                        <InputError :message="form.errors.type" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="outcome" :value="__('Outcome (Optional)')" />
                        <SelectInput
                            id="outcome"
                            v-model="form.outcome"
                            class="mt-1 block w-full"
                        >
                            <option value="">{{ __('None') }}</option>
                            <option value="connected">{{ __('Connected') }}</option>
                            <option value="no_answer">{{ __('No Answer') }}</option>
                            <option value="busy">{{ __('Busy') }}</option>
                            <option value="left_message">{{ __('Left Message') }}</option>
                            <option value="interested">{{ __('Interested') }}</option>
                            <option value="not_interested">{{ __('Not Interested') }}</option>
                        </SelectInput>
                        <InputError :message="form.errors.outcome" class="mt-2" />
                    </div>
                </div>

                <div>
                    <InputLabel for="subject" :value="__('Subject')" />
                    <TextInput
                        id="subject"
                        v-model="form.subject"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        placeholder="Details of call/meeting..."
                    />
                    <InputError :message="form.errors.subject" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="description" :value="form.type === 'note' ? __('Notes / Message') : __('Description')" />
                    <TextArea
                        id="description"
                        v-model="form.description"
                        class="mt-1 block w-full"
                        rows="6"
                        :placeholder="form.type === 'note' ? __('Any additional details about this lead...') : __('Add description...')"
                    />
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="duration" :value="__('Duration (min)')" />
                        <TextInput
                            id="duration"
                            v-model="form.duration_minutes"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.duration_minutes" class="mt-2" />
                    </div>
                     <div>
                        <InputLabel for="scheduled_at" :value="__('Date/Time')" />
                        <TextInput
                            id="scheduled_at"
                            v-model="form.scheduled_at"
                            type="datetime-local"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.scheduled_at" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="$emit('close')">
                        {{ __('Cancel') }}
                    </SecondaryButton>

                    <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        {{ activity ? __('Update Activity') : __('Log Activity') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
