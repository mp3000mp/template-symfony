<script lang="ts">
import { HTMLElementEvent } from '@/utils/types'
import AdminUserRow from '@/views/admin/UserRow.vue'
import { onMounted, computed, defineComponent, reactive, ref } from 'vue'
import { useStore } from '@/store'

export default defineComponent({
  name: 'AdminUsersPage',
  components: {
    AdminUserRow
  },
  setup () {
    const store = useStore()

    const isFormOpened = ref(false)
    const emptyUser = {
      email: '',
      isEnabled: true,
      roles: ['ROLE_USER'],
      username: ''
    }
    let tmpUser = reactive(Object.assign({}, emptyUser))

    const userRequests = computed(() => store.state.users.actionRequest)
    const users = computed(() => store.state.users.users)

    function closeForm () {
      isFormOpened.value = false
    }
    async function addUser () {
      try {
        await store.dispatch('users/addUser', tmpUser)
        closeForm()
      } catch (err) {
        alert(err)
      }
    }
    function initTmpUser () {
      tmpUser = Object.assign(tmpUser, emptyUser)
    }
    function initForm () {
      initTmpUser()
      isFormOpened.value = true
    }
    function getAllUsers () {
      store.dispatch('users/getAll')
    }
    function setRoles (event: HTMLElementEvent<HTMLSelectElement>) {
      if (event.target.value === 'admin') {
        tmpUser.roles.push('ROLE_ADMIN')
      } else {
        const i = tmpUser.roles.indexOf('ROLE_ADMIN')
        if (i > -1) {
          tmpUser.roles.splice(i, 1)
        }
      }
    }

    onMounted(() => {
      getAllUsers()
    })

    return { isFormOpened, userRequests, users, tmpUser, addUser, setRoles, initForm, closeForm }
  }
})
</script>

<template>
  <div class="container">
    <h1>Users</h1>
    <div class="table-responsive">
      <table class="table">
        <tr>
          <th>Username</th>
          <th>Email</th>
          <th>roles</th>
          <th>Action</th>
        </tr>
        <admin-user-row
          v-for="user in users"
          :key="user.id"
          :user="user"
        />
      </table>
      <form
        v-if="isFormOpened"
        @submit.prevent="addUser"
        class="d-flex flex-column basic-form"
      >
        <label class="form-label" for="newUsername"></label>
        <input class="form-control" required="required" type="text" placeholder="Username" v-model="tmpUser.username" id="newUsername" />
        <label class="form-label" for="newEmail"></label>
        <input class="form-control" required="required" type="email" placeholder="Email" v-model="tmpUser.email" id="newEmail" />
        <label class="form-label" for="newRoles"></label>
        <select class="form-control form-select mb-2" id="newRoles" @change="setRoles">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
        <input type="submit" class="btn btn-primary mb-2" value="Create user" />
        <input type="submit" class="btn btn-secondary" value="Cancel" @click.prevent="closeForm" />
        <span class="err">{{ userRequests.addUser.message }}</span>
      </form>
      <button
        v-else
        class="btn btn-primary"
        @click.prevent="initForm"
      >New user</button>
    </div>
  </div>
</template>
