import { createSlice } from "@reduxjs/toolkit";

const initialState = {
  page: "home",
  userStatus: _AS.userStatus,
};

export const userSlice = createSlice({
  name: "user",
  initialState,
  reducers: {
    setPage: (state, action) => {
      state.page = action.payload;
    },
    setOnBoarding: (state, action) => {
      state.userStatus.onBoarding = action.payload;
    },
  },
});

export const { setPage, setOnBoarding } = userSlice.actions;

export default userSlice.reducer;
