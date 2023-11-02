import { configureStore } from "@reduxjs/toolkit";
import { setupListeners } from "@reduxjs/toolkit/query";
import userReducer from "../features/user/userSlice";
import { serviceApi } from "../services/service";

export const store = configureStore({
  reducer: {
    user: userReducer,
    [serviceApi.reducerPath]: serviceApi.reducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware().concat(serviceApi.middleware),
});

setupListeners(store.dispatch);
