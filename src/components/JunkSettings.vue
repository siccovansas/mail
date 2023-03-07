<!--
  - @copyright 2023 Daniel Kesselberg <mail@danielkesselberg.de>
  -
  - @author 2023 Daniel Kesselberg <mail@danielkesselberg.de>
  -
  - @license AGPL-3.0-or-later
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
	<div>
		<p>
			{{ t('mail', 'Junk messages are saved in:') }}
		</p>
		<MailboxInlinePicker v-model="junkMailbox" :account="account" :disabled="saving" />

		<NcCheckboxRadioSwitch :checked="account.moveJunkToMailbox"
			:disabled="saving"
			type="switch"
			@update:checked="saveMoveJunkToMailbox">
			{{ t('mail', 'Move messages to Junk folder') }}
		</NcCheckboxRadioSwitch>
	</div>
</template>

<script>
import logger from '../logger'
import MailboxInlinePicker from './MailboxInlinePicker'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch'

export default {
	name: 'JunkSettings',
	components: {
		MailboxInlinePicker,
		NcCheckboxRadioSwitch,
	},
	props: {
		account: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			saving: false,
		}
	},
	computed: {
		junkMailbox: {
			get() {
				const mailbox = this.$store.getters.getMailbox(this.account.junkMailboxId)
				if (!mailbox) {
					return
				}
				return mailbox.databaseId
			},
			async set(junkMailboxId) {
				logger.debug('setting junk mailbox to ' + junkMailboxId)
				this.saving = true
				try {
					await this.$store.dispatch('patchAccount', {
						account: this.account,
						data: {
							junkMailboxId,
						},
					})
				} catch (error) {
					logger.error('could not set junk mailbox', {
						error,
					})
				} finally {
					this.saving = false
				}
			},
		},
	},
	methods: {
		async saveMoveJunkToMailbox(moveJunkToMailbox) {
			try {
				await this.$store.dispatch('patchAccount', {
					account: this.account,
					data: {
						moveJunkToMailbox,
					},
				})
			} catch (error) {
				logger.error('could not set move junk to mailbox', {
					error,
				})
			} finally {
				this.saving = false
			}
		},
	},
}
</script>

<style lang="scss" scoped>
.button.icon-rename {
	background-color: transparent;
	border: none;
	opacity: 0.3;

	&:hover,
	&:focus {
		opacity: 1;
	}
}
</style>
