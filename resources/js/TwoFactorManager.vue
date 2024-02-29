<template>
    <b-overlay :show="loading">
        <div v-if="!twoFactorSettings.can_enable">
            <p>You are not allowed to enable two-factor authentication.</p>
        </div>
        <div v-else>
            <h5 v-if="twoFactorSettings.authenticator && !twoFactorSettings.authenticator_confirmed">
                Finish enabling two factor authentication.
            </h5>
            <h5 v-else-if="isEnabled">
                You have enabled two factor authentication.
            </h5>
            <h5 v-else-if="!isEnabled">
                You have not enabled two factor authentication.
            </h5>

            <p>
                When two factor authentication is enabled, you will be prompted for a secure, random token during
                authentication. You may retrieve this token from your phone's Authenticator application or Email Inbox (if enabled).
            </p>

            <div v-if="isEnabled">
                <div v-if="recoveryCodes" class="mt-5">
                    <p>Here are your recovery codes, if you get locked out of your account you may use one to get back in.</p>
                    <div class="row">
                        <div class="col-6">
                            <div class="row">
                                <div v-for="code in recoveryCodes" class="col-6">
                                    <div class="p-3">
                                        {{ code }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="twoFactorSettings.types.authenticator" class="mt-5">
                <h6><strong>Authenticator</strong></h6>
                <b-button v-if="!twoFactorSettings.authenticator" variant="primary"
                          @click="setTwoFactor('authenticator', true)">
                    Enable Authenticator 2FA
                </b-button>

                <div v-if="twoFactorSettings.authenticator && !twoFactorSettings.authenticator_confirmed">
                    <div v-if="qrCode" class="mb-5">
                        <p>
                            To finish enabling two factor authentication, scan the following QR code using your phone's authenticator
                            application or enter the setup key and provide the generated OTP code.
                        </p>

                        <div v-html="qrCode"></div>
                    </div>

                    <div v-if="secretKey">
                        <p>
                            <strong>Setup Key:</strong> {{ secretKey }}
                        </p>
                    </div>

                    <b-form @submit.prevent="confirmAuthenticator">
                        <b-form-group label="Confirmation Code">
                            <b-form-input v-model="confirmation.code"></b-form-input>
                        </b-form-group>
                        <b-form-group>
                            <b-button type="submit" variant="primary">
                                Confirm Authenticator
                            </b-button>

                            <b-button variant="danger" class="ml-2"
                                      @click="setTwoFactor('authenticator', false)">
                                Cancel
                            </b-button>
                        </b-form-group>
                    </b-form>
                </div>

                <div v-if="twoFactorSettings.authenticator && twoFactorSettings.authenticator_confirmed">
                    <b-button variant="danger"
                              @click="setTwoFactor('authenticator', false)">
                        Disable Authenticator 2FA
                    </b-button>
                </div>
            </div>

            <div v-if="twoFactorSettings.types.email" class="mt-5">
                <h6><strong>Email</strong></h6>
                <b-button v-if="!twoFactorSettings.email" variant="primary"
                          @click="setTwoFactor('email', true)">
                    Enable Email 2FA
                </b-button>
                <b-button v-if="twoFactorSettings.email" variant="danger"
                          @click="setTwoFactor('email', false)">
                    Disable Email 2FA
                </b-button>
            </div>

            <div v-if="twoFactorSettings.types.sms" class="mt-5">
                <h6><strong>SMS</strong></h6>
                <b-button v-if="!twoFactorSettings.sms" variant="primary"
                          @click="setTwoFactor('sms', true)">
                    Enable SMS 2FA
                </b-button>
                <b-button v-if="twoFactorSettings.sms" variant="danger"
                          @click="setTwoFactor('sms', false)">
                    Disable SMS 2FA
                </b-button>
            </div>
        </div>
    </b-overlay>
</template>

<script>
import axios from 'axios'
import toastr from 'toastr'

export default {
    name: 'TwoFactorManager',
    data() {
        return {
            loading: true,
            twoFactorSettings: {
                can_enable: false,
                enabled: false,
                sms: false,
                email: false,
                authenticator: false,
                authenticator_confirmed: false,
                enforce: false,
                recently_enabled: false,
                types: {
                    email: false,
                    sms: false,
                    authenticator: false,
                    recovery_codes: false,
                }
            },
            confirmation: {
                code: null,
            },
            secretKey: null,
            qrCode: null,
            recoveryCodes: null,
        }
    },
    computed: {
        isEnabled() {
            if (this.twoFactorSettings.enforce) {
                return true
            }

            return this.twoFactorSettings.enabled
        }
    },
    mounted() {
        this.fetchSettings()
    },
    methods: {
        fetchSettings() {
            this.loading = true

            return axios.get('/api/two-factor')
                .then((rsp) => {
                    this.twoFactorSettings = rsp.data

                    if (this.twoFactorSettings.authenticator && !this.twoFactorSettings.authenticator_confirmed) {
                        this.fetchSecretKey()
                        this.fetchQrCode()
                    }

                    if (this.twoFactorSettings.recently_enabled) {
                        this.fetchRecoveryCodes()
                    }
                }).catch((err) => {
                    toastr.error(err.response.data.message)
                }).finally(() => {
                    this.loading = false
                })
        },
        setTwoFactor(key, value) {
            const data = {
                email: this.twoFactorSettings.email,
                sms: this.twoFactorSettings.sms,
                authenticator: this.twoFactorSettings.authenticator,
            }

            data[key] = value

            this.loading = true

            return axios.put('/api/two-factor', data)
                .then(() => {
                    if (key === 'authenticator' && value) {
                        Promise.all([
                            this.fetchSecretKey(),
                            this.fetchQrCode(),
                        ]).then(() => {
                            this.fetchSettings()
                        })
                    } else {
                        this.fetchSettings()
                    }
                }).catch((err) => {
                    toastr.error(err.response.data.message)
                })
        },
        fetchSecretKey() {
            return axios.get('/api/two-factor/show-secret').then((rsp) => {
                this.secretKey = rsp.data.secret
            })
        },
        fetchQrCode() {
            return axios.get('/api/two-factor/show-qrcode').then((rsp) => {
                this.qrCode = rsp.data.svg
            })
        },
        fetchRecoveryCodes() {
            return new Promise((resolve) => {
                if(!this.twoFactorSettings.types.recovery_codes) {
                    this.recoveryCodes = null;
                    return resolve()
                }

                return axios.get('/api/two-factor/show-recovery-codes').then((rsp) => {
                    this.recoveryCodes = rsp.data.codes
                }).finally(() => {
                    resolve()
                })
            })

        },
        confirmAuthenticator() {
            this.loading = true

            axios.post('/api/two-factor/confirm', this.confirmation).then(() => {
                this.fetchSettings()
                this.secretKey = null
                this.qrCode = null
                this.confirmation.code = null
            }).catch((err) => {
                toastr.error(err.response.data.message)
            }).finally(() => {
                this.loading = false
            })
        }
    }
}
</script>
