<template>
  <v-container 
    grid-list-md 
    text-xs-center>
    <v-layout 
      align-center 
      row 
      wrap>
      <v-flex xs2><v-icon medium>smartphone</v-icon></v-flex>
      <v-flex xs10>
        <v-text-field
          v-model="name"
          :rules="nameRules"
          label="验证码"
          required
          @blur="scrollTo"
        />
      </v-flex>
    </v-layout>
    <v-layout 
      align-center 
      row 
      wrap>
      <v-flex xs2><v-icon medium>smartphone</v-icon></v-flex>
      <v-flex xs10>
        <v-text-field
          v-model="name"
          :rules="nameRules"
          label="验证码"
          required
          @blur="scrollTo"
        />
      </v-flex>
    </v-layout>
    <v-layout 
      align-center 
      row 
      wrap>
      <v-flex xs2><v-icon medium>headset</v-icon></v-flex><v-flex xs6>
        <v-text-field
          v-model="name"
          :rules="nameRules"
          label="验证码"
          required
          @blur="scrollTo"
        />
      </v-flex>
      <v-flex xs4>
        <v-btn 
          small
          color="light-blue"
          class="white--text">
          获取
        </v-btn>
      </v-flex>
    </v-layout>
    <v-layout 
      align-center 
      row 
      wrap>
      <v-flex xs2><v-icon medium>face</v-icon></v-flex>
      <v-flex xs8>
        <v-select
          label="喵喵喵"
        />
      </v-flex>
      <v-flex xs2>
        <v-icon>keyboard_arrow_right</v-icon>
      </v-flex>
    </v-layout>
    <v-layout 
      align-center 
      row 
      wrap>
      <v-flex xs2><v-icon medium>smartphone</v-icon></v-flex>
      <v-flex xs10><v-text-field
        v-model="name"
        :rules="nameRules"
        label="验证码"
        required
        @blur="scrollTo"
      />
      </v-flex>
    </v-layout>
    
    <v-layout 
      align-center 
      row 
      wrap>
      <v-flex xs2><v-icon medium>smartphone</v-icon></v-flex>
      <v-flex xs10><v-text-field
        v-model="name"
        :rules="nameRules"
        label="验证码"
        required
        @blur="scrollTo"
      />
      </v-flex>
    </v-layout>
    
    <v-layout 
      align-center 
      row 
      wrap>
      <v-flex xs2><v-icon medium>smartphone</v-icon></v-flex>
      <v-flex xs10><v-text-field
        v-model="name"
        :rules="nameRules"
        label="验证码"
        required
        @blur="scrollTo"
      />
      </v-flex>
    </v-layout>
  </v-container>
</template>
