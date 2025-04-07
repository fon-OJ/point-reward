import { ref } from "vue";
import axios from "axios";

export const user = ref(null);
export const fetchUser = async () => {
  try {
    const res = await axios.get("/profile", {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    });
    user.value = res.data;
  } catch (err) {
    console.error("Fetch user failed:", err);
    user.value = null;
  }
};
export const clearUser = () => {
  user.value = { name: "", email: "", points: 0 };
};

export const updateProfile = async (userData) => {
  const response = await axios.put("/profile/update", userData, {
    headers: {
      Authorization: `Bearer ${localStorage.getItem("token")}`,
    },
  });
  return response.data;
};
